import DB_LogReader from '@/classes/DB_LogReader'
import { PATHS } from '@/libs/assets'
import { PATTERN_DATE } from '@/libs/validation'

const LOG_LEVELS = [ 'DEBUG', 'INFO', 'WARNING', 'ERROR' ] as const

export type LogLevel = typeof LOG_LEVELS[number]

export type LogRow = {
  id: number
  timestamp: string
  level: LogLevel
  message: string | null
  status: number | null
  duration_ms: number | null
  request_method: string | null
  request_path: string | null
  request_referer: string | null
  request_origin: string | null
  request_client_ip: string | null
  request_user_agent: string | null
  request_id: string | null
  context_json?: string | null
  error_name: string | null
  error_message: string | null
  error_stack?: string | null
  error_cause_json?: string | null
}

export type LogIndexItem = {
  id: number
  timestamp: string
  level: LogLevel
  message: string | null
  status: number | null
  duration_ms: number | null
  request: {
    method: string | null
    path: string | null
    referer: string | null
    origin: string | null
    client_ip: string | null
    user_agent: string | null
    id: string | null
  } | null
  error: {
    name: string | null
    message: string | null
  } | null
}

export class LogRouteInputError extends Error {}

let database: DB_LogReader | undefined

export function getLogDatabase(): DB_LogReader
{
  database ??= new DB_LogReader(`${PATHS.DATA}/log.sqlite`)
  return database
}

export function parseDate(value: string, field: 'from' | 'to'): string
{
  if (!(new RegExp(PATTERN_DATE).test(value)))
  {
    throw new LogRouteInputError(`Invalid log ${field}.`)
  }

  const timestamp = new Date(`${value}T00:00:00.000Z`)
  if (
    Number.isNaN(timestamp.getTime())
    || timestamp.toISOString().slice(0, 10) !== value
  )
  {
    throw new LogRouteInputError(`Invalid log ${field}.`)
  }
  if (field === 'to') timestamp.setUTCHours(23, 59, 59, 999)
  return timestamp.toISOString()
}

export function parseJSON(value?: string | null): unknown
{
  if (!value) return null
  try
  {
    return JSON.parse(value)
  }
  catch
  {
    return null
  }
}

export function toNumber(value: unknown, fallback = 0): number
{
  const number = Number(value)
  return Number.isFinite(number) ? number : fallback
}

export function toIndexItem(row: LogRow): LogIndexItem
{
  const hasRequest = row.request_method || row.request_path || row.request_referer
    || row.request_origin || row.request_client_ip || row.request_user_agent || row.request_id
  const request = hasRequest ? {
    method: row.request_method,
    path: row.request_path,
    referer: row.request_referer,
    origin: row.request_origin,
    client_ip: row.request_client_ip,
    user_agent: row.request_user_agent,
    id: row.request_id,
  } : null
  const error = (row.error_name || row.error_message) ? {
    name: row.error_name,
    message: row.error_message,
  } : null
  return {
    id: row.id,
    timestamp: row.timestamp,
    level: row.level,
    message: row.message,
    status: row.status,
    duration_ms: row.duration_ms,
    request,
    error,
  }
}
