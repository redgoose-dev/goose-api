import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import { parseJSON } from '@/libs/objects'
import * as helper from './__helper'
import * as fileHelper from '@/routes/file/__helper'
import type { CheckinToken } from '@/libs/verify'
import type { ArticleModel } from './__model'

type GetItemParams = {
  srl: number
  query: ArticleModel['getItemQuery']
  token: CheckinToken
}

export default async function getItem({ srl, query, token }: GetItemParams)
{
  try
  {
    // set assets
    let _table = `${DB.TABLE.ARTICLE} AS a`
    let _join: string[] = []
    let _where: string[] = []
    let _values: ZZ = {}
    const _field = query.field ? query.field.split(',').map(s => (`a.${s}`)).join(',') : 'a.*'

    // set base params
    _where.push(`AND a.srl = ${srl}`)
    if (query.app)
    {
      _join.push(`JOIN ${DB.TABLE.NEST} AS n ON n.srl = a.nest_srl`)
      _where.push(`AND n.app_srl = ${query.app}`)
    }
    if (token.public)
    {
      _where.push(`AND mode LIKE \'${helper.STATUS.PUBLIC}\'`)
    }
    else
    {
      _where.push(`AND mode NOT LIKE \'${helper.STATUS.READY}\'`)
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
    if (_mod.check('app') && item.data.nest_srl)
    {
      item.data.app = db.getData({
        table: `${DB.TABLE.APP} AS a`,
        field: 'a.srl,a.code,a.name',
        join: `JOIN ${DB.TABLE.NEST} AS n ON a.srl = n.app_srl`,
        where: `n.srl = ${item.data.nest_srl}`,
      }).data
    }
    // MOD / up-hit,up-star
    if (_mod.check('up-hit') || _mod.check('up-star'))
    {
      let _set = []
      if (_mod.check('up-hit'))
      {
        _set.push('hit = hit + 1')
        if (item.data.hit !== undefined) item.data.hit += 1
      }
      if (_mod.check('up-star'))
      {
        _set.push('star = star + 1')
        if (item.data.star !== undefined) item.data.star += 1
      }
      db.editData({
        table: DB.TABLE.ARTICLE,
        where: `srl = ${srl}`,
        set: _set,
      })
    }
    // MOD / count-file
    if (_mod.check('count-file'))
    {
      item.data.count_file = fileHelper.count({
        where: [
          `AND module LIKE \'${fileHelper.MODULE.ARTICLE}\'`,
          `AND module_srl = ${srl}`,
        ],
      })
    }

    return item.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Article.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
