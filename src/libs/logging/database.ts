import DB_Log from '@/classes/DB_Log'
import { createRecordLogEntry } from '@/libs/logging/record'
import type { RecordLogEntry } from '@/libs/logging/record'
import type { Transport } from 'logixlysia'

const DEFAULT_BATCH_SIZE = 50
const DEFAULT_FLUSH_INTERVAL_MS = 1000
const DEFAULT_MAX_QUEUE_SIZE = 5000

export type RecordDatabasePolicy = {
  path: string
  retentionDays: number
  batchSize?: number
  flushIntervalMs?: number
  maxQueueSize?: number
}

type RecordDatabaseTransportOptions = {
  policy: RecordDatabasePolicy
  now?: () => Date
}

export type RecordDatabaseTransport = Transport & {
  flush(): Promise<void>
  close(): Promise<void>
}

function getPositiveInteger(value: number | undefined, fallback: number): number
{
  return Number.isFinite(value) && Number(value) > 0
    ? Math.floor(Number(value))
    : fallback
}

function isImportant(entry: RecordLogEntry): boolean
{
  return entry.level === 'WARNING' || entry.level === 'ERROR'
}

export function createRecordDatabaseTransport(
  options: RecordDatabaseTransportOptions,
): RecordDatabaseTransport
{
  const { policy, now = () => new Date() } = options
  const batchSize = getPositiveInteger(policy.batchSize, DEFAULT_BATCH_SIZE)
  const flushIntervalMs = getPositiveInteger(
    policy.flushIntervalMs,
    DEFAULT_FLUSH_INTERVAL_MS,
  )
  const maxQueueSize = getPositiveInteger(
    policy.maxQueueSize,
    DEFAULT_MAX_QUEUE_SIZE,
  )
  const database = new DB_Log({
    path: policy.path,
    retentionDays: policy.retentionDays,
    now,
  })

  const queue: RecordLogEntry[] = []
  let timer: ReturnType<typeof setTimeout> | undefined
  let closed = false
  let droppedCount = 0

  function clearFlushTimer(): void
  {
    if (!timer) return
    clearTimeout(timer)
    timer = undefined
  }

  function reportDroppedEntries(): void
  {
    if (droppedCount <= 0) return
    console.warn(`[logging] Dropped ${droppedCount} database log entries because the queue was full.`)
    droppedCount = 0
  }

  function writeBatch(): void
  {
    const entries = queue.splice(0, batchSize)
    if (entries.length === 0) return

    try
    {
      database.insert(entries)
      reportDroppedEntries()
    }
    catch (error)
    {
      // 로거를 다시 호출하면 재귀할 수 있으므로 DB 기록 오류는 콘솔에 직접 알린다.
      console.error('[logging] Failed to record database logs.', error)
    }
  }

  function scheduleFlush(delay = flushIntervalMs): void
  {
    if (closed || timer) return
    timer = setTimeout(() => {
      timer = undefined
      writeBatch()
      if (queue.length > 0) scheduleFlush(0)
    }, delay)
    timer.unref?.()
  }

  function enqueue(entry: RecordLogEntry): void
  {
    if (queue.length >= maxQueueSize)
    {
      // 오류와 경고가 들어오면 대기 중인 낮은 레벨 로그를 하나 양보한다.
      const replaceIndex = isImportant(entry)
        ? queue.findIndex(item => !isImportant(item))
        : -1
      if (replaceIndex >= 0)
      {
        queue.splice(replaceIndex, 1)
      }
      else
      {
        droppedCount++
        return
      }
      droppedCount++
    }

    queue.push(entry)
    if (queue.length >= batchSize)
    {
      clearFlushTimer()
      scheduleFlush(0)
    }
    else
    {
      scheduleFlush()
    }
  }

  return {
    log(level, message, meta = {})
    {
      if (closed) return
      enqueue(createRecordLogEntry({
        level,
        message,
        meta,
        now: now(),
      }))
    },

    async flush()
    {
      clearFlushTimer()
      while (queue.length > 0) writeBatch()
    },

    async close()
    {
      if (closed) return
      clearFlushTimer()
      while (queue.length > 0) writeBatch()
      closed = true
      database.close()
    },
  }
}
