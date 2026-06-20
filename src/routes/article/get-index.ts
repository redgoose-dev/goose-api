import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import { parseJSON } from '@/libs/objects'
import * as appHelper from '@/routes/app/__helper'
import * as nestHelper from '@/routes/nest/__helper'
import * as categoryHelper from '@/routes/category/__helper'
import * as tagHelper from '@/routes/tag/__helper'
import * as fileHelper from '@/routes/file/__helper'
import * as helper from './__helper'
import type { CheckinToken } from '@/libs/verify'
import type { ArticleModel } from './__model'

type GetIndexParams = {
  query: ArticleModel['getIndexQuery']
  token: CheckinToken
}

export default async function getIndex({ query, token }: GetIndexParams)
{
  try
  {
    // set assets
    let _table = `${DB.TABLE.ARTICLE} AS a`
    let _where: string[] = []
    let _values: ZZ = {}
    let _join: string[] = []
    let _order: string | undefined
    let _sort: string | undefined
    const _field = query.field ? query.field.split(',') : ''

    // set base params
    if (query.app)
    {
      _join.push(`JOIN ${DB.TABLE.NEST} AS n ON n.srl = a.nest_srl`)
      _where.push(`AND n.app_srl = ${query.app}`)
    }
    if (query.nest)
    {
      _where.push(`AND nest_srl = ${query.nest}`)
    }
    if (query.category)
    {
      if (query.category > 0) _where.push(`AND category_srl = ${query.category}`)
      else _where.push(`AND category_srl IS NULL`)
    }
    if (query.q)
    {
      _where.push(`AND (a.title LIKE \'%${query.q}%\' OR content LIKE \'%${query.q}%\')`)
    }
    if (token.public)
    {
      _where.push(`AND mode LIKE \'${helper.STATUS.PUBLIC}\'`)
    }
    else if (query.mode)
    {
      _where.push(`AND mode LIKE \'${query.mode}\'`)
    }
    else
    {
      _where.push(`AND mode NOT LIKE \'${helper.STATUS.READY}\'`)
    }
    if (query.tag)
    {
      const _tags = query.tag.split(',').join(',')
      _where.push(`AND a.srl IN (SELECT mt.module_srl FROM ${DB.TABLE.MAP_TAG} AS mt WHERE mt.module LIKE \'${tagHelper.MODULE.ARTICLE}\' AND mt.tag_srl IN (${_tags}))`)
    }
    if (query.duration)
    {
      const _duration: string[] = query.duration.split(',')
      const _rangeMap: ZZ = {
        day: '1 day',
        week: '7 day',
        month: '1 month',
        year: '1 year',
      }
      const _spDate = _duration[3] && /^\d{4}-\d{2}-\d{2}/.test(_duration[3]) ? _duration[3] : 'now'
      const _durationField = _duration[1]
      const _durationRange = _rangeMap[_duration[2] as string] ?? '1 day'
      switch (_duration[0])
      {
        case 'old':
          _where.push(`AND (${_durationField} BETWEEN DATETIME($durationDate, "-${_durationRange}", "localtime") AND DATETIME($durationDate, "-1 day", "localtime"))`)
          break
        case 'new':
          _where.push(`AND (${_durationField} BETWEEN DATETIME($durationDate, "+1 day", "localtime") AND DATETIME($durationDate, "+${_durationRange}", "localtime"))`)
          break
      }
      _values['$durationDate'] = _spDate
    }

    // get count
    const count = db.getCount({
      table: _table,
      field: 'COUNT(DISTINCT a.srl) AS count',
      where: _where,
      join: _join,
      values: _values,
    })
    if (count.data <= 0) throw new ServiceError('No data', { status: 204 })

    // set random
    if (query.random)
    {
      _order = `ABS(((a.srl * $random * 999) + 579) % 1000)`
      _sort = ''
      _values['$random'] = Number(query.random)
    }
    else if (query.order)
    {
      _order = query.order
      _sort = query.sort || 'desc'
    }

    // get index
    let index = db.getIndex({
      table: _table,
      prefix: 'DISTINCT',
      field: _field,
      where: _where,
      join: _join,
      order: _order,
      sort: _sort,
      page: query.page,
      size: query.size,
      values: _values,
    })

    // set MOD
    const _mod: MOD = new MOD(query.mod)

    // transform index
    index.data = index.data.map((item: ZZ) => {
      if (item.json) item.json = parseJSON(item.json)
      // MOD / app
      if (_mod.check('app') && item.nest_srl)
      {
        item.app = appHelper.getItemFromNest(item.nest_srl, 'srl,code,name')
      }
      // MOD / nest
      if (_mod.check('nest') && item.nest_srl)
      {
        item.nest = nestHelper.getItem(item.nest_srl, 'srl,code,name')
      }
      // MOD / category
      if (_mod.check('category') && item.category_srl)
      {
        item.category = categoryHelper.getItem(item.category_srl, 'srl,name')
      }
      // MOD / tag
      if (_mod.check('tag'))
      {
        item.tag = tagHelper.getIndex(tagHelper.MODULE.ARTICLE, item.srl)
      }
      // MOD / file
      if (_mod.check('file'))
      {
        item.file = fileHelper.getIndex({
          module: fileHelper.MODULE.ARTICLE,
          module_srl: item.srl,
          field: 'srl,code,name',
        })
      }
      return item
    })

    return {
      total: count.data,
      index: index.data,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Article index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
