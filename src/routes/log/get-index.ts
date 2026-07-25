import ServiceError from '@/classes/ServiceError'
import { LogRouteInputError, parseDate, toIndexItem } from './__helper'
import type DB_LogReader from '@/classes/DB_LogReader'
import type { LogModel } from './__model'
import type { LogLevel, LogRow } from './__helper'

const DEFAULT_INDEX_SIZE = 50
const MAX_INDEX_SIZE = 100
const LOG_LEVELS: LogLevel[] = [ 'DEBUG', 'INFO', 'WARNING', 'ERROR' ]

type GetIndexParams = {
  query: LogModel['getIndexQuery']
  database: DB_LogReader
}

type QueryValues = Record<string, string | number>

function getIndexSize(size?: number): number
{
  if (size === undefined) return DEFAULT_INDEX_SIZE
  if (!Number.isSafeInteger(size) || size < 1 || size > MAX_INDEX_SIZE)
  {
    throw new LogRouteInputError('Invalid log index size.')
  }
  return size
}

function getLevels(value?: string): LogLevel[]
{
  if (!value) return []
  const levels = Array.from(new Set(value.split(',')))
  if (levels.some(level => !LOG_LEVELS.includes(level as LogLevel)))
  {
    throw new LogRouteInputError('Invalid log level.')
  }
  return levels as LogLevel[]
}

function escapeLike(value: string): string
{
  return value.replace(/[\\%_]/g, '\\$&')
}

function encodeCursor(row: Pick<LogRow, 'timestamp' | 'id'>): string
{
  return Buffer.from(JSON.stringify([ row.timestamp, row.id ])).toString('base64url')
}

function decodeCursor(cursor: string): Pick<LogRow, 'timestamp' | 'id'>
{
  try
  {
    const value = JSON.parse(Buffer.from(cursor, 'base64url').toString('utf8'))
    if (!Array.isArray(value) || value.length !== 2 || typeof value[0] !== 'string' || !Number.isSafeInteger(value[1]) || value[1] < 1)
    {
      throw new Error()
    }
    const timestamp = new Date(value[0])
    if (Number.isNaN(timestamp.getTime()) || timestamp.toISOString() !== value[0]) throw new Error()
    return {
      timestamp: value[0],
      id: value[1],
    }
  }
  catch
  {
    throw new LogRouteInputError('Invalid log cursor.')
  }
}

export default async function getIndex({ query, database }: GetIndexParams)
{
  try
  {
    const size = getIndexSize(query.size)
    const where: string[] = []
    const values: QueryValues = {}
    if (query.total !== undefined && ![ 0, 1 ].includes(query.total))
    {
      throw new LogRouteInputError('Invalid log total option.')
    }

    const levels = getLevels(query.level)
    if (levels.length > 0)
    {
      const fields = levels.map((level, index) => {
        const key = `level_${index}`
        values[key] = level
        return `$${key}`
      })
      where.push(`AND level IN (${fields.join(', ')})`)
    }
    if (query.from)
    {
      values.from = parseDate(query.from, 'from')
      where.push('AND timestamp >= $from')
    }
    if (query.to)
    {
      values.to = parseDate(query.to, 'to')
      where.push('AND timestamp <= $to')
    }
    if (typeof values.from === 'string' && typeof values.to === 'string' && values.from > values.to)
    {
      throw new LogRouteInputError('Log from must not be later than to.')
    }
    if (query.status !== undefined)
    {
      values.status = query.status
      where.push('AND status = $status')
    }
    if (query.method)
    {
      values.method = query.method.toUpperCase()
      where.push('AND request_method = $method')
    }
    if (query.path)
    {
      values.path = `%${escapeLike(query.path)}%`
      where.push(`AND request_path LIKE $path ESCAPE '\\'`)
    }
    if (query.request_id)
    {
      values.request_id = query.request_id
      where.push('AND request_id = $request_id')
    }
    if (query.q)
    {
      values.q = `%${escapeLike(query.q)}%`
      where.push(`
        AND (
          message LIKE $q ESCAPE '\\'
          OR error_message LIKE $q ESCAPE '\\'
          OR request_path LIKE $q ESCAPE '\\'
          OR request_id LIKE $q ESCAPE '\\'
        )
      `)
    }
    const indexWhere = [ ...where ]
    const indexValues: QueryValues = { ...values }
    if (query.cursor)
    {
      const cursor = decodeCursor(query.cursor)
      indexValues.cursor_timestamp = cursor.timestamp
      indexValues.cursor_id = cursor.id
      indexWhere.push(`
        AND (
          timestamp < $cursor_timestamp
          OR (timestamp = $cursor_timestamp AND id < $cursor_id)
        )
      `)
    }

    indexValues.limit = size + 1
    const indexSQL = `
      SELECT
        id,
        timestamp,
        level,
        message,
        status,
        duration_ms,
        request_method,
        request_path,
        request_id,
        error_name,
        error_message
      FROM log
      WHERE 1 = 1
        ${indexWhere.join('\n')}
      ORDER BY timestamp DESC, id DESC
      LIMIT $limit
    `
    const countSQL = `SELECT COUNT(*) AS count FROM log WHERE 1 = 1 ${where.join('\n')}`
    const result = query.total === 1 ? database.transaction(() => ({
      total: database.getData<{ count: number }>(countSQL, values)?.count ?? 0,
      rows: database.getIndex<LogRow>(indexSQL, indexValues),
    })) : undefined
    const rows = result?.rows ?? database.getIndex<LogRow>(indexSQL, indexValues)

    const hasNext = rows.length > size
    const pageRows = hasNext ? rows.slice(0, size) : rows
    const lastRow = pageRows.at(-1)

    const assets: {
      has_next: boolean
      cursor: string | null
    } = {
      has_next: hasNext,
      cursor: hasNext && lastRow ? encodeCursor(lastRow) : null,
    }
    const data: {
      index: ReturnType<typeof toIndexItem>[]
      total?: number
      assets: typeof assets
    } = {
      index: pageRows.map(toIndexItem),
      assets,
    }
    if (query.total === 1) data.total = result?.total ?? 0

    return data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Log index.', {
      status: _e instanceof LogRouteInputError ? 400 : _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
