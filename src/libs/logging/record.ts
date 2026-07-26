import type { LogLevel } from 'logixlysia'
import { normalizeLogLevel } from '@/libs/logging/level'

const VALIDATION_TEXT_MAX_LENGTH = 500

export type RecordLogEntry = {
  timestamp: string
  level: LogLevel
  message?: string
  status?: unknown
  duration_ms?: number
  request?: {
    method?: string
    path?: string
    referer?: string
    origin?: string
    client_ip?: string
    user_agent?: string
  }
  context?: Record<string, unknown>
  error?: {
    name?: string
    message?: string
    stack?: string
    cause?: unknown
  }
}

type CreateRecordLogEntryOptions = {
  level: LogLevel
  message: string
  meta: Record<string, unknown>
  now: Date
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

function getError(error: unknown): RecordLogEntry['error']
{
  if (!isRecord(error) && !(error instanceof Error)) return undefined

  const source = error as Error & Record<string, unknown>
  const result: NonNullable<RecordLogEntry['error']> = {}
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

type ValidationCause = {
  source?: string
  property?: string
  message?: string
  summary?: string
}

function getValidationCause(message: string): ValidationCause
{
  const parsed = parseValidationMessage(message)
  const result: ValidationCause = {
    source: getShortText(parsed?.on, 50),
    property: getShortText(parsed?.property, 200),
    message: getShortText(parsed?.message ?? message),
    summary: getShortText(parsed?.summary),
  }
  return Object.fromEntries(
    Object.entries(result).filter(([, value ]) => value !== undefined),
  ) as ValidationCause
}

function getRequest(meta: Record<string, unknown>): RecordLogEntry['request']
{
  if (!isRecord(meta.request)) return undefined

  const request = meta.request
  const method = (typeof request.method === 'string') ? request.method : undefined
  const url = (typeof request.url === 'string') ? request.url : undefined
  const referer = getRequestValue(request, 'referer')
  const origin = getRequestValue(request, 'origin')
  const clientIP = getRequestValue(request, 'client_ip')
  const userAgent = getRequestValue(request, 'user_agent')
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
  if (!(method || path || referer || origin || clientIP || userAgent)) return undefined
  return {
    method,
    path,
    referer,
    origin,
    client_ip: clientIP,
    user_agent: userAgent,
  }
}

function getRequestValue(request: Record<string, unknown>, key: string): string | undefined
{
  const value = request[key]
  if (typeof value === 'string' && value) return value

  if (key === 'client_ip')
  {
    const forwarded = getRequestHeader(request, 'x-forwarded-for')
    const clientIP = forwarded?.split(',')[0]?.trim()
    if (clientIP) return clientIP
    return getRequestHeader(request, 'x-real-ip')
  }
  return getRequestHeader(request, key.replaceAll('_', '-'))
}

function getRequestHeader(request: Record<string, unknown>, name: string): string | undefined
{
  const headers = request.headers
  if (!headers || typeof headers !== 'object') return undefined
  const get = (headers as { get?: unknown }).get
  if (typeof get === 'function')
  {
    const header = get.call(headers, name)
    return typeof header === 'string' && header ? header : undefined
  }
  const header = Object.entries(headers).find(([ headerKey ]) => headerKey.toLowerCase() === name)
  return typeof header?.[1] === 'string' && header[1] ? header[1] : undefined
}

function getDuration(beforeTime: unknown): number | undefined
{
  if (typeof beforeTime !== 'bigint') return undefined
  if (beforeTime === BigInt(0)) return 0
  const duration = Number(process.hrtime.bigint() - beforeTime) / 1_000_000
  return Number(duration.toFixed(3))
}

export function createRecordLogEntry({ level, message, meta, now }: CreateRecordLogEntryOptions): RecordLogEntry
{
  const context = getContext(meta)
  const isValidationError = Number(meta.status) === 422
  const validationCause = isValidationError ? getValidationCause(message) : undefined
  return {
    timestamp: now.toISOString(),
    level: normalizeLogLevel(level, meta.status),
    message: validationCause?.message || validationCause?.summary || message || undefined,
    status: meta.status,
    duration_ms: getDuration(meta.beforeTime),
    request: getRequest(meta),
    context: context && Object.keys(context).length > 0 ? context : undefined,
    // 422는 schema와 입력값이 포함된 Error/stack 대신 정리된 cause만 기록한다.
    error: isValidationError ? { cause: validationCause } : getError(meta.error),
  }
}
