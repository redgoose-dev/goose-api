import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import { parseJSON } from '@/libs/objects'
import type { FileModel } from './__model'

type GetIndexParams = {
  query: FileModel['getIndexQuery']
  service: Service
}

export default async function getIndex({ query, service }: GetIndexParams)
{
  try
  {
    // set assets
    const _table = `${DB.TABLE.FILE} AS f`
    const _field = query.field ? query.field.split(',') : ''
    let _where: string[] = []
    let _values: ZZ = {}
    let _join: string[] = []
    if (query.module)
    {
      _where.push(`AND module LIKE $module`)
      _values['$module'] = query.module
    }
    if (query.module && query.module_srl)
    {
      _where.push(`AND module_srl = $module_srl`)
      _values['$module_srl'] = query.module_srl
    }
    if (query.name)
    {
      _where.push(`AND name LIKE '%' || $name || '%'`)
      _values['$name'] = query.name
    }
    if (query.mime)
    {
      _where.push(`AND mime LIKE '%' || $mime || '%'`)
      _values['$mime'] = query.mime
    }

    // get count
    const count = db.getCount({
      table: _table,
      where: _where,
      join: _join,
      values: _values,
    })
    if (count.data <= 0) throw new ServiceError('No data', { status: 204 })

    // get index
    const index = db.getIndex({
      table: _table,
      field: _field,
      where: _where,
      join: _join,
      order: Boolean(query.order || query.sort) ? query.order : 'srl',
      sort: Boolean(query.order || query.sort) ? query.sort : 'desc',
      page: query.page ?? 1,
      size: query.size ?? service.preference['file.index.size'],
      values: _values,
    })
    const _index = index.data.map((o: ZZ) => {
      return {
        ...o,
        json: o.json ? parseJSON(o.json) : undefined,
        path: undefined,
      }
    })

    return {
      total: count.data,
      index: _index,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get File index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
