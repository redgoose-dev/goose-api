import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import { parseJSON } from '@/libs/objects'
import * as articleHelper from '@/routes/article/__helper'
import type { NestModel } from './__model'

type GetItemParams = {
  srl?: number
  code?: string
  query: NestModel['getItemQuery']
}

export default async function getItem({ srl, code, query }: GetItemParams)
{
  try
  {
    // set assets
    let _table = `${DB.TABLE.NEST} AS n`
    let _join: string[] = []
    let _where: string[] = []
    let _values: ZZ = {}
    const _field = query.field ? query.field.split(',') : ''

    // set base params
    if (srl)
    {
      _where.push(`AND srl = ${srl}`)
    }
    else if (code)
    {
      _where.push(`AND code LIKE \'${code}\'`)
    }
    if (query.app)
    {
      _where.push(`AND app_srl = ${query.app}`)
    }

    // get data
    let item = db.getData({
      table: _table,
      field: _field,
      join: _join,
      where: _where,
      values: _values,
    })
    if (!item.data) throw new ServiceError('No data', { status: 204 })

    // set json data
    if (item.data.json)
    {
      item.data.json = parseJSON(item.data.json)
    }

    // set MOD
    const _mod: MOD = new MOD(query.mod)

    // MOD / app
    if (_mod.check('app'))
    {
      item.data.app = db.getData({
        table: DB.TABLE.APP,
        field: 'srl,code,name',
        where: `srl = ${item.data.app_srl}`,
      }).data
    }

    // MOD / count-article
    if (_mod.check('count-article'))
    {
      item.data.count_article = articleHelper.count({
        where: `nest_srl = ${item.data.srl}`,
      })
    }

    return item.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Nest item.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
