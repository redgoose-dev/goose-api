import ServiceError from '@/classes/ServiceError'
import { LogRouteInputError, parseJSON, toIndexItem } from './__helper'
import type DB_LogReader from '@/classes/DB_LogReader'
import type { LogRow } from './__helper'

type GetItemParams = {
  id: number
  database: DB_LogReader
}

export default async function getItem({ id, database }: GetItemParams)
{
  try
  {
    if (!Number.isSafeInteger(id) || id < 1)
    {
      throw new LogRouteInputError('Invalid log id.')
    }

    const row = database.getData<LogRow>(`SELECT * FROM log WHERE id = $id`, { id })
    if (!row) throw new ServiceError('No data', { status: 404 })

    const cause = parseJSON(row.error_cause_json)
    const error = (row.error_name || row.error_message || row.error_stack || cause) ? {
      name: row.error_name,
      message: row.error_message,
      stack: row.error_stack ?? null,
      cause,
    } : null

    return {
      ...toIndexItem(row),
      context: parseJSON(row.context_json),
      error,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Log.', {
      status: _e instanceof LogRouteInputError ? 400 : _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
