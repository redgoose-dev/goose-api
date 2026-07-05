import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import { TagModel } from './__model'

type GetIndexParams = {
  query: TagModel['getIndexQuery']
  service: Service
}

export default async function getIndex({ query, service }: GetIndexParams)
{
  try
  {
    // set assets
    let _field = []
    let _where: string[] = []
    let _join: string[] = []
    let _values: ZZ = {}
    if (query.name)
    {
      _where.push(`AND t.name LIKE \'%${query.name}%\'`)
    }
    if (query.module)
    {
      _field.push(`t.*`)
      _join.push(`JOIN ${DB.TABLE.MAP_TAG} AS mt ON mt.tag_srl = t.srl`)
      _where.push(`AND mt.module LIKE \'${query.module}\'`)
    }
    if (query.module && query.module_srl)
    {
      _where.push(`AND mt.module_srl = ${query.module_srl}`)
    }

    // get count
    const count = db.getCount({
      table: `${DB.TABLE.TAG} AS t`,
      field: `COUNT(DISTINCT t.srl) AS count`,
      where: _where,
      join: _join,
      values: _values,
    })
    if (count.data <= 0) throw new ServiceError('No data', { status: 204 })

    // get index
    const index = db.getIndex({
      table: `${DB.TABLE.TAG} AS t`,
      prefix: 'DISTINCT',
      field: _field,
      where: _where,
      join: _join,
      order: query.order,
      page: query.page ?? 1,
      size: query.size ?? service.preference['tag.index.size'],
      values: _values,
    })

    return {
      total: count.data,
      index: index.data,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Tag index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
