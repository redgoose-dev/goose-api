import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import type { CommentModel } from './__model'

type GetIndexParams = {
  query: CommentModel['getIndexQuery']
  service: Service
}

export default async function getItem({ query, service }: GetIndexParams)
{
  try
  {
    // set assets
    let _where: string[] = []
    let _values: ZZ = {}
    const _field = query.field ? query.field.split(',') : ''

    // set base params
    if (query.module)
    {
      _where.push(`AND module LIKE $module`)
      _values['$module'] = query.module
    }
    if (query.module && query.module_srl)
    {
      _where.push(`AND module_srl = ${query.module_srl}`)
    }
    if (query.q)
    {
      _where.push(`AND content LIKE '%' || $content || '%'`)
      _values['$content'] = query.q
    }

    // get count
    const count = db.getCount({
      table: DB.TABLE.COMMENT,
      where: _where,
      values: _values,
    })
    if (count.data <= 0) throw new ServiceError('No data', { status: 204 })

    // get index
    let index = db.getIndex({
      table: DB.TABLE.COMMENT,
      field: _field,
      where: _where,
      order: Boolean(query.order || query.sort) ? query.order : 'srl',
      sort: Boolean(query.order || query.sort) ? query.sort : 'desc',
      page: query.page ?? 1,
      size: query.size ?? service.preference['comment.index.size'],
      values: _values,
    })

    return {
      total: count.data,
      index: index.data,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Comment index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
