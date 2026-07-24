import { Database } from 'bun:sqlite'
import { mkdirSync } from 'node:fs'
import { dirname } from 'node:path'
import type { RecordLogEntry } from '@/libs/logging/record'

const DAY_IN_MS = 24 * 60 * 60 * 1000
const DEFAULT_CLEANUP_INTERVAL_MS = 6 * 60 * 60 * 1000

export type LogDatabaseOptions = {
  path: string
  retentionDays: number
  cleanupIntervalMs?: number
  now?: () => Date
}

function toText(value: unknown): string | null
{
  if (value === undefined || value === null) return null
  if (typeof value === 'string') return value
  if (typeof value === 'number' || typeof value === 'boolean' || typeof value === 'bigint')
  {
    return String(value)
  }
  return toJSON(value)
}

function toNumber(value: unknown): number | null
{
  if (value === undefined || value === null || value === '') return null
  const result = Number(value)
  return Number.isFinite(result) ? result : null
}

function toJSON(value: unknown): string | null
{
  if (value === undefined || value === null) return null
  try
  {
    return JSON.stringify(value) ?? null
  }
  catch
  {
    return null
  }
}

function getRequestId(context?: Record<string, unknown>): string | null
{
  return toText(context?.request_id)
}

/**
 * 로그 전용 SQLite 저장소.
 *
 * 서비스 데이터베이스와 연결 및 트랜잭션을 공유하지 않아 로그 기록이
 * 콘텐츠 작업을 잠그지 않도록 한다. 조회용 연결은 같은 파일을 별도로 열 수 있고,
 * WAL 모드에서는 기록 중에도 기존 스냅샷을 계속 읽을 수 있다.
 */
export default class DB_Log {

  readonly path: string
  readonly retentionDays: number

  #cleanupIntervalMs: number
  #now: () => Date
  #db?: Database
  #insertMany?: (entries: readonly RecordLogEntry[]) => void
  #deleteExpired?: (expirationTimestamp: string) => void
  #lastCleanupAt = 0

  constructor(options: LogDatabaseOptions)
  {
    if (!options.path) throw new Error('Log database path is required.')
    if (!(options.retentionDays > 0))
    {
      throw new Error('Log database retention days must be greater than 0.')
    }

    this.path = options.path
    this.retentionDays = options.retentionDays
    this.#cleanupIntervalMs = options.cleanupIntervalMs ?? DEFAULT_CLEANUP_INTERVAL_MS
    this.#now = options.now ?? (() => new Date())
  }

  #connect(): Database
  {
    if (this.#db) return this.#db

    mkdirSync(dirname(this.path), { recursive: true })
    const db = new Database(this.path, {
      create: true,
      readwrite: true,
      strict: true,
    })

    // WAL은 기록 트랜잭션과 조회 연결이 서로 기다리는 시간을 줄인다.
    db.exec('PRAGMA journal_mode = WAL')
    db.exec('PRAGMA synchronous = NORMAL')
    db.exec('PRAGMA busy_timeout = 5000')
    db.exec('PRAGMA temp_store = MEMORY')
    db.exec('PRAGMA wal_autocheckpoint = 1000')
    db.exec(`
      CREATE TABLE IF NOT EXISTS log (
        id INTEGER PRIMARY KEY,
        timestamp TEXT NOT NULL,
        level TEXT NOT NULL CHECK (level IN ('DEBUG', 'INFO', 'WARNING', 'ERROR')),
        error_code TEXT,
        message TEXT,
        status INTEGER,
        duration_ms REAL,
        request_method TEXT,
        request_path TEXT,
        request_id TEXT,
        context_json TEXT,
        error_name TEXT,
        error_message TEXT,
        error_stack TEXT,
        error_cause_json TEXT
      );

      CREATE INDEX IF NOT EXISTS log_timestamp_idx
        ON log (timestamp DESC, id DESC);

      CREATE INDEX IF NOT EXISTS log_level_timestamp_idx
        ON log (level, timestamp DESC, id DESC);

      CREATE INDEX IF NOT EXISTS log_error_code_idx
        ON log (error_code)
        WHERE error_code IS NOT NULL;

      CREATE INDEX IF NOT EXISTS log_request_id_idx
        ON log (request_id)
        WHERE request_id IS NOT NULL;
    `)

    const insert = db.prepare(`
      INSERT INTO log (
        timestamp,
        level,
        error_code,
        message,
        status,
        duration_ms,
        request_method,
        request_path,
        request_id,
        context_json,
        error_name,
        error_message,
        error_stack,
        error_cause_json
      ) VALUES (
        $timestamp,
        $level,
        $error_code,
        $message,
        $status,
        $duration_ms,
        $request_method,
        $request_path,
        $request_id,
        $context_json,
        $error_name,
        $error_message,
        $error_stack,
        $error_cause_json
      )
    `)

    const transaction = db.transaction((entries: readonly RecordLogEntry[]) => {
      for (const entry of entries)
      {
        insert.run({
          timestamp: entry.timestamp,
          level: entry.level,
          error_code: toText(entry.error_code),
          message: entry.message ?? null,
          status: toNumber(entry.status),
          duration_ms: entry.duration_ms ?? null,
          request_method: entry.request?.method ?? null,
          request_path: entry.request?.path ?? null,
          request_id: getRequestId(entry.context),
          context_json: toJSON(entry.context),
          error_name: entry.error?.name ?? null,
          error_message: entry.error?.message ?? null,
          error_stack: entry.error?.stack ?? null,
          error_cause_json: toJSON(entry.error?.cause),
        })
      }
    })

    const deleteExpired = db.prepare('DELETE FROM log WHERE timestamp < $timestamp')

    this.#db = db
    this.#insertMany = transaction.immediate
    this.#deleteExpired = (expirationTimestamp) => {
      deleteExpired.run({ timestamp: expirationTimestamp })
    }
    return db
  }

  insert(entries: readonly RecordLogEntry[]): void
  {
    if (entries.length === 0) return
    this.#connect()
    this.#insertMany!(entries)
    this.#cleanupIfNeeded()
  }

  #cleanupIfNeeded(): void
  {
    const now = this.#now()
    if (now.getTime() - this.#lastCleanupAt < this.#cleanupIntervalMs) return

    const expirationTimestamp = new Date(
      now.getTime() - this.retentionDays * DAY_IN_MS,
    ).toISOString()
    this.#deleteExpired!(expirationTimestamp)
    this.#lastCleanupAt = now.getTime()
  }

  close(): void
  {
    if (!this.#db) return
    this.#db.close()
    this.#db = undefined
    this.#insertMany = undefined
    this.#deleteExpired = undefined
  }
}
