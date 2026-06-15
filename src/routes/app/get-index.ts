import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import type { AppModel } from './__model'

type GetIndexParams = {
  query: AppModel['getIndexQuery']
}

export default async function getIndex({ query }: GetIndexParams)
{
  try
  {
    // set assets
    let _where = []
    let _values: ZZ = {}
    if (query.code)
    {
      _where.push(`AND code LIKE $code`)
      _values['$code'] = query.code
    }
    if (query.name)
    {
      _where.push(`AND name LIKE '%' || $name || '%'`)
      _values['$name'] = query.name
    }
    // get total
    const total = db.getCount({
      table: DB.TABLE.APP,
      where: _where,
      values: _values,
    })
    if (!(total.data as number > 0))
    {
      throw new ServiceError('No data', { status: 204 })
    }
    // set field
    const _field = query.field ? query.field.split(',') : ''
    // get index data
    let index = db.getIndex({
      table: DB.TABLE.APP,
      field: _field,
      where: _where,
      values: _values,
      order: Boolean(query.order || query.sort) ? query.order : 'srl',
      sort: Boolean(query.order || query.sort) ? query.sort : 'desc',
    })
    // set mod
    const _mod: MOD = new MOD(query.mod)
    // MOD / count-nest
    if (_mod.check('count-nest'))
    {
      // TODO: Nest 데이터 쌓이면 만들자
    }
    // MOD / count-article
    if (_mod.check('count-article'))
    {
      // TODO: Article 데이터 쌓이면 만들자
    }
    return {
      total: total.data,
      index: index.data,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get App index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
