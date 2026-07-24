import ServiceError from '@/classes/ServiceError'
import { LogRouteInputError, parseDate, toNumber } from './__helper'
import type DB_LogReader from '@/classes/DB_LogReader'
import type { LogModel } from './__model'

const DEFAULT_SUMMARY_DURATION_MS = 24 * 60 * 60 * 1000
const HOUR_IN_MS = 60 * 60 * 1000

type SummaryInterval = 'hour' | 'day'
type GetSummaryParams = {
  query: LogModel['getSummaryQuery']
  database: DB_LogReader
  now?: () => Date
}
type SummaryAssets = {
  from: string
  to: string
  interval: SummaryInterval
}

function getEmptySummary(assets: SummaryAssets)
{
  return {
    total: 0,
    levels: {
      debug: 0,
      info: 0,
      warning: 0,
      error: 0,
    },
    statuses: {
      '1xx': 0,
      '2xx': 0,
      '3xx': 0,
      '4xx': 0,
      '5xx': 0,
      unknown: 0,
    },
    duration_ms: {
      average: null,
      max: null,
    },
    latest_error_at: null,
    timeline: [],
    assets,
  }
}

export default async function getSummary({ query, database, now = () => new Date() }: GetSummaryParams)
{
  try
  {
    const to = query.to ? parseDate(query.to, 'to') : now().toISOString()
    const from = query.from ? parseDate(query.from, 'from') : new Date(new Date(to).getTime() - DEFAULT_SUMMARY_DURATION_MS).toISOString()
    if (from > to)
    {
      throw new LogRouteInputError('Log from must not be later than to.')
    }

    const duration = new Date(to).getTime() - new Date(from).getTime()
    const interval = query.interval ?? (duration <= 48 * HOUR_IN_MS ? 'hour' : 'day')
    const assets: SummaryAssets = { from, to, interval }
    const values = { from, to }

    const bucket = interval === 'hour' ? `STRFTIME('%Y-%m-%dT%H:00:00.000Z', timestamp)` : `STRFTIME('%Y-%m-%dT00:00:00.000Z', timestamp)`
    const result = database.transaction(() => {
      const summary = database.getData<Record<string, unknown>>(`
        SELECT
          COUNT(*) AS total,
          SUM(CASE WHEN level = 'DEBUG' THEN 1 ELSE 0 END) AS debug,
          SUM(CASE WHEN level = 'INFO' THEN 1 ELSE 0 END) AS info,
          SUM(CASE WHEN level = 'WARNING' THEN 1 ELSE 0 END) AS warning,
          SUM(CASE WHEN level = 'ERROR' THEN 1 ELSE 0 END) AS error,
          SUM(CASE WHEN status BETWEEN 100 AND 199 THEN 1 ELSE 0 END) AS status_1xx,
          SUM(CASE WHEN status BETWEEN 200 AND 299 THEN 1 ELSE 0 END) AS status_2xx,
          SUM(CASE WHEN status BETWEEN 300 AND 399 THEN 1 ELSE 0 END) AS status_3xx,
          SUM(CASE WHEN status BETWEEN 400 AND 499 THEN 1 ELSE 0 END) AS status_4xx,
          SUM(CASE WHEN status BETWEEN 500 AND 599 THEN 1 ELSE 0 END) AS status_5xx,
          SUM(CASE WHEN status IS NULL OR status < 100 OR status >= 600 THEN 1 ELSE 0 END) AS status_unknown,
          ROUND(AVG(duration_ms), 3) AS duration_average,
          MAX(duration_ms) AS duration_max,
          MAX(CASE WHEN level = 'ERROR' THEN timestamp END) AS latest_error_at
        FROM log
        WHERE timestamp >= $from AND timestamp <= $to
      `, values)
      const timelineRows = database.getIndex<Record<string, unknown>>(`
        SELECT
          ${bucket} AS timestamp,
          COUNT(*) AS total,
          SUM(CASE WHEN level = 'WARNING' THEN 1 ELSE 0 END) AS warning,
          SUM(CASE WHEN level = 'ERROR' THEN 1 ELSE 0 END) AS error
        FROM log
        WHERE timestamp >= $from AND timestamp <= $to
        GROUP BY 1
        ORDER BY timestamp ASC
      `, values)
      return { summary, timelineRows }
    })
    if (!result?.summary) return getEmptySummary(assets)
    const { summary, timelineRows } = result

    return {
      total: toNumber(summary.total),
      levels: {
        debug: toNumber(summary.debug),
        info: toNumber(summary.info),
        warning: toNumber(summary.warning),
        error: toNumber(summary.error),
      },
      statuses: {
        '1xx': toNumber(summary.status_1xx),
        '2xx': toNumber(summary.status_2xx),
        '3xx': toNumber(summary.status_3xx),
        '4xx': toNumber(summary.status_4xx),
        '5xx': toNumber(summary.status_5xx),
        unknown: toNumber(summary.status_unknown),
      },
      duration_ms: {
        average: summary.duration_average === null ? null : toNumber(summary.duration_average),
        max: summary.duration_max === null ? null : toNumber(summary.duration_max),
      },
      latest_error_at: typeof summary.latest_error_at === 'string' ? summary.latest_error_at : null,
      timeline: timelineRows.map(row => ({
        timestamp: String(row.timestamp),
        total: toNumber(row.total),
        warning: toNumber(row.warning),
        error: toNumber(row.error),
      })),
      assets,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Log summary.', {
      status: _e instanceof LogRouteInputError ? 400 : _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
