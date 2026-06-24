import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import MOD from '@/classes/MOD'
import * as nestHelper from '@/routes/nest/__helper'
import * as helper from './__helper'
import type { AppModel } from './__model'

type GetIndexParams = {
  query: AppModel['getIndexQuery']
  service: Service
}

export default async function getIndex({ query, service }: GetIndexParams)
{
  try
  {
    // set assets
    let _where = []
    let _values: ZZ = {}
    const _field = query.field ? query.field.split(',') : ''

    // set base params
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

    // get index data
    let index = db.getIndex({
      table: DB.TABLE.APP,
      field: _field,
      where: _where,
      order: Boolean(query.order || query.sort) ? query.order : 'srl',
      sort: Boolean(query.order || query.sort) ? query.sort : 'desc',
      page: query.page ?? 1,
      size: query.size ?? service.preference['app.index.size'],
      values: _values,
    })

    // set mod
    const _mod: MOD = new MOD(query.mod)

    // transform index
    index.data = index.data.map((item: ZZ) => {
      // MOD / count-nest
      if (_mod.check('count-nest'))
      {
        item.count_nest = nestHelper.count({
          where: `app_srl = ${item.srl}`,
        })
      }
      // MOD / count-article
      if (_mod.check('count-article'))
      {
        item.count_article = helper.countArticle(item.srl)
      }
      return item
    })

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
