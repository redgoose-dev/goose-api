import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import MOD from '@/classes/MOD'
import { parseJSON } from '@/libs/objects'
import * as articleHelper from '@/routes/article/__helper'
import type { NestModel } from './__model'

type GetIndexParams = {
  query: NestModel['getIndexQuery']
  service: Service
}

export default async function getIndex({ query, service }: GetIndexParams)
{
  try
  {
    // set assets
    let _table = `${DB.TABLE.NEST} AS n`
    let _where: string[] = []
    let _values: ZZ = {}
    let _join: string[] = []
    const _field = query.field ? query.field.split(',') : ''

    // set base params
    if (query.app)
    {
      _where.push(`AND app_srl = ${query.app}`)
    }
    if (query.code)
    {
      _where.push(`AND code LIKE \'${query.code}\'`)
    }
    if (query.name)
    {
      _where.push(`AND name LIKE \'%${query.name}%\'`)
    }

    // get count
    const count = db.getCount({
      table: _table,
      where: _where,
    })
    if (count.data <= 0) throw new ServiceError('No data', { status: 204 })

    // get index
    let index = db.getIndex({
      table: _table,
      field: _field,
      where: _where,
      join: _join,
      order: query.order,
      page: query.page ?? 1,
      size: query.size ?? service.preference['nest.index.size'],
      values: _values,
    })

    // set MOD
    const _mod: MOD = new MOD(query.mod)

    // transform index
    index.data = index.data.map((o: ZZ) => {
      // MOD / app
      if (_mod.check('app') && o.app_srl)
      {
        o.app = db.getData({
          table: DB.TABLE.APP,
          field: 'srl,code,name',
          where: `srl = ${o.app_srl}`,
        }).data
      }
      // MOD / count-article
      if (_mod.check('count-article'))
      {
        o.count_article = articleHelper.count({
          where: `nest_srl = ${o.srl}`,
        })
      }
      return {
        ...o,
        json: parseJSON(o.json),
      }
    })

    return {
      total: count.data,
      index: index.data,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Nest index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
