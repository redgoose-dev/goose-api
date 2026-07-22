import { appendFile, mkdir, readdir } from 'node:fs/promises'
import { join } from 'node:path'
import type { LogLevel, Transport } from 'logixlysia'

const DAY_IN_MS = 24 * 60 * 60 * 1000
const VALIDATION_TEXT_MAX_LENGTH = 500

export type RecordFilePolicy = {
  directory: string
  maxFileSizeBytes: number
  retentionDays: number
  maxFilesPerLevel: number
}

type RecordFileTransportOptions = {
  policy: RecordFilePolicy
  service?: string
  now?: () => Date
}

type FileLogEntry = {
  timestamp: string
  service?: string
  level: LogLevel
  error_code?: unknown
  message?: string
  status?: unknown
  duration_ms?: number
  request?: {
    method?: string
    path?: string
  }
  context?: Record<string, unknown>
  error?: {
    name?: string
    message?: string
    stack?: string
    cause?: unknown
  }
  validation?: {
    source?: string
    property?: string
    message?: string
    summary?: string
  }
}

type LogFile = {
  name: string
  path: string
  mtimeMs: number
}

type LogFileOrder = {
  date: string
  sequence: number
}

function isRecord(value: unknown): value is Record<string, unknown>
{
  return typeof value === 'object' && value !== null && !Array.isArray(value)
}

function toSnakeCase(value: string): string
{
  return value
    .replace(/([a-z0-9])([A-Z])/g, '$1_$2')
    .replace(/[\s-]+/g, '_')
    .toLowerCase()
}

function normalizeValue(value: unknown, seen = new WeakSet<object>()): unknown
{
  if (value === null || value === undefined) return value
  if (value instanceof Date) return value.toISOString()
  if (typeof value === 'bigint') return value.toString()
  if (typeof value !== 'object') return value
  if (seen.has(value)) return '[Circular]'

  seen.add(value)
  try
  {
    if (Array.isArray(value))
    {
      return value.map(item => normalizeValue(item, seen))
    }
    return Object.fromEntries(
      Object.entries(value).map(([ key, item ]) => [
        toSnakeCase(key),
        normalizeValue(item, seen),
      ]),
    )
  }
  finally
  {
    seen.delete(value)
  }
}

function getContext(meta: Record<string, unknown>): Record<string, unknown> | undefined
{
  if (!isRecord(meta.context)) return undefined
  const context = normalizeValue(meta.context)
  return isRecord(context) ? context : undefined
}

function getError(error: unknown): FileLogEntry['error']
{
  if (!isRecord(error) && !(error instanceof Error)) return undefined

  const source = error as Error & Record<string, unknown>
  const result: NonNullable<FileLogEntry['error']> = {}
  if (source.name) result.name = String(source.name)
  if (source.message) result.message = String(source.message)
  if (source.stack) result.stack = String(source.stack)
  if (source.cause !== undefined) result.cause = normalizeValue(source.cause)
  return Object.keys(result).length > 0 ? result : undefined
}

function getShortText(value: unknown, maxLength = VALIDATION_TEXT_MAX_LENGTH): string | undefined
{
  if (typeof value !== 'string' || !value) return undefined
  return value.length > maxLength
    ? `${value.slice(0, maxLength)}…`
    : value
}

function parseValidationMessage(message: string): Record<string, unknown> | undefined
{
  try
  {
    const parsed = JSON.parse(message)
    return isRecord(parsed) ? parsed : undefined
  }
  catch
  {
    return undefined
  }
}

function getValidation(message: string): FileLogEntry['validation']
{
  const parsed = parseValidationMessage(message)
  const result: NonNullable<FileLogEntry['validation']> = {
    source: getShortText(parsed?.on, 50),
    property: getShortText(parsed?.property, 200),
    message: getShortText(parsed?.message ?? message),
    summary: getShortText(parsed?.summary),
  }
  return Object.fromEntries(
    Object.entries(result).filter(([, value ]) => value !== undefined),
  ) as FileLogEntry['validation']
}

function getRequest(meta: Record<string, unknown>): FileLogEntry['request']
{
  if (!isRecord(meta.request)) return undefined

  const method = typeof meta.request.method === 'string'
    ? meta.request.method
    : undefined
  const url = typeof meta.request.url === 'string'
    ? meta.request.url
    : undefined
  let path = url
  if (url)
  {
    try
    {
      const parsedUrl = new URL(url)
      path = `${parsedUrl.pathname}${parsedUrl.search}`
    }
    catch {}
  }
  return method || path ? { method, path } : undefined
}

function getDuration(beforeTime: unknown): number | undefined
{
  if (typeof beforeTime !== 'bigint') return undefined
  if (beforeTime === BigInt(0)) return 0
  const duration = Number(process.hrtime.bigint() - beforeTime) / 1_000_000
  return Number(duration.toFixed(3))
}

function createFileLogEntry(
  service: string | undefined,
  level: LogLevel,
  message: string,
  meta: Record<string, unknown>,
  now: Date,
): FileLogEntry
{
  const context = getContext(meta)
  const isValidationError = Number(meta.status) === 422
  const validation = isValidationError ? getValidation(message) : undefined
  const errorCode = level === 'ERROR'
    ? meta.error_code ?? meta.errorCode ?? context?.error_code ?? context?.code
    : undefined

  // 에러 코드는 검색하기 쉽도록 context가 아닌 최상위 error_code에 한 번만 기록한다.
  if (level === 'ERROR' && context)
  {
    delete context.code
    delete context.error_code
  }

  return {
    timestamp: now.toISOString(),
    service,
    level,
    error_code: errorCode,
    message: validation?.message || validation?.summary || message || undefined,
    status: meta.status,
    duration_ms: getDuration(meta.beforeTime),
    request: getRequest(meta),
    context: context && Object.keys(context).length > 0 ? context : undefined,
    // 422는 validation 정보만으로 추적하고 schema와 입력값이 포함된 Error/stack은 기록하지 않는다.
    error: isValidationError ? undefined : getError(meta.error),
    validation,
  }
}

function formatDate(date: Date): string
{
  const pad = (value: number) => String(value).padStart(2, '0')
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`
}

function getFileSequence(fileName: string, prefix: string): number | undefined
{
  if (fileName === `${prefix}.jsonl`) return 0
  const match = fileName.match(new RegExp(`^${prefix}\\.(\\d+)\\.jsonl$`))
  return match?.[1] === undefined ? undefined : Number(match[1])
}

function getLogFileOrder(fileName: string): LogFileOrder
{
  const match = fileName.match(/-(\d{4}-\d{2}-\d{2})(?:\.(\d+))?\.jsonl$/)
  return {
    date: match?.[1] ?? '',
    sequence: Number(match?.[2] ?? 0),
  }
}

function compareNewestLogFile(a: LogFile, b: LogFile): number
{
  const modifiedOrder = b.mtimeMs - a.mtimeMs
  if (modifiedOrder !== 0) return modifiedOrder

  // 같은 밀리초에 회전된 파일은 파일명의 날짜와 회전 번호로 생성 순서를 판별한다.
  const aOrder = getLogFileOrder(a.name)
  const bOrder = getLogFileOrder(b.name)
  return bOrder.date.localeCompare(aOrder.date) || bOrder.sequence - aOrder.sequence
}

async function selectLogFile(
  policy: RecordFilePolicy,
  level: LogLevel,
  date: Date,
  entrySize: number,
): Promise<{ path: string, isNew: boolean }>
{
  const prefix = `${level.toLowerCase()}-${formatDate(date)}`
  const fileNames = await readdir(policy.directory)
  const candidates = fileNames
    .map(name => ({ name, sequence: getFileSequence(name, prefix) }))
    .filter((item): item is { name: string, sequence: number } => item.sequence !== undefined)
    .sort((a, b) => a.sequence - b.sequence)

  const current = candidates.at(-1)
  if (!current)
  {
    return {
      path: join(policy.directory, `${prefix}.jsonl`),
      isNew: true,
    }
  }

  const currentPath = join(policy.directory, current.name)
  const currentSize = Bun.file(currentPath).size
  if (currentSize === 0 || currentSize + entrySize <= policy.maxFileSizeBytes)
  {
    return { path: currentPath, isNew: false }
  }

  return {
    path: join(policy.directory, `${prefix}.${current.sequence + 1}.jsonl`),
    isNew: true,
  }
}

async function getLevelLogFiles(policy: RecordFilePolicy, level: LogLevel): Promise<LogFile[]>
{
  const pattern = new RegExp(`^${level.toLowerCase()}-\\d{4}-\\d{2}-\\d{2}(?:\\.\\d+)?\\.jsonl$`)
  const fileNames = (await readdir(policy.directory)).filter(name => pattern.test(name))
  return fileNames.map(name => {
    const path = join(policy.directory, name)
    return { name, path, mtimeMs: Bun.file(path).lastModified }
  })
}

async function deleteLogFile(path: string): Promise<void>
{
  try
  {
    await Bun.file(path).delete()
  }
  catch (error)
  {
    // 정리 도중 이미 삭제된 파일은 성공한 것으로 처리한다.
    if (isRecord(error) && error.code === 'ENOENT') return
    throw error
  }
}

async function cleanupLogFiles(policy: RecordFilePolicy, level: LogLevel, now: Date): Promise<void>
{
  const expirationTime = now.getTime() - policy.retentionDays * DAY_IN_MS
  const files = await getLevelLogFiles(policy, level)
  const expiredFiles = files.filter(file => file.mtimeMs < expirationTime)
  await Promise.all(expiredFiles.map(file => deleteLogFile(file.path)))

  const expiredPaths = new Set(expiredFiles.map(file => file.path))
  const retainedFiles = files
    .filter(file => !expiredPaths.has(file.path))
    .sort(compareNewestLogFile)

  // 파일 수가 제한을 넘으면 수정 시각이 가장 오래된 파일부터 제거한다.
  await Promise.all(
    retainedFiles
      .slice(policy.maxFilesPerLevel)
      .map(file => deleteLogFile(file.path)),
  )
}

function validatePolicy(policy: RecordFilePolicy): void
{
  if (!policy.directory) throw new Error('Log directory is required.')
  if (!(policy.maxFileSizeBytes > 0)) throw new Error('Log max file size must be greater than 0.')
  if (!(policy.retentionDays > 0)) throw new Error('Log retention days must be greater than 0.')
  if (!(policy.maxFilesPerLevel > 0)) throw new Error('Log max files per level must be greater than 0.')
}

export function createRecordFileTransport(options: RecordFileTransportOptions): Transport
{
  const { policy, service, now = () => new Date() } = options
  validatePolicy(policy)

  const writeQueues = new Map<LogLevel, Promise<void>>()
  const cleanedLevels = new Set<LogLevel>()

  async function write(level: LogLevel, message: string, meta: Record<string, unknown>): Promise<void>
  {
    const loggedAt = now()
    await mkdir(policy.directory, { recursive: true })

    if (!cleanedLevels.has(level))
    {
      await cleanupLogFiles(policy, level, loggedAt)
      cleanedLevels.add(level)
    }

    const entry = createFileLogEntry(service, level, message, meta, loggedAt)
    const line = `${JSON.stringify(entry)}\n`
    const target = await selectLogFile(policy, level, loggedAt, Buffer.byteLength(line))
    await appendFile(target.path, line, { encoding: 'utf8' })

    if (target.isNew)
    {
      await cleanupLogFiles(policy, level, loggedAt)
    }
  }

  return {
    async log(level, message, meta = {})
    {
      // 같은 레벨의 동시 기록을 순서대로 처리해 파일 회전과 정리가 충돌하지 않게 한다.
      const previous = writeQueues.get(level) ?? Promise.resolve()
      const current = previous.catch(() => {}).then(() => write(level, message, meta))
      writeQueues.set(level, current)

      try
      {
        await current
      }
      catch (error)
      {
        // 로거를 다시 호출하면 재귀할 수 있으므로 파일 기록 오류는 콘솔에 직접 알린다.
        console.error('[logging] Failed to record a log file.', error)
      }
      finally
      {
        if (writeQueues.get(level) === current) writeQueues.delete(level)
      }
    },
  }
}
