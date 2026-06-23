import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import * as articleHelper from '@/routes/article/__helper'
import * as jsonHelper from '@/routes/json/__helper'
import * as helper from './__helper'
import type { CategoryModel } from './__model'

type GetIndexParams = {
  query: CategoryModel['getIndexQuery']
}

export default async function getIndex({ query }: GetIndexParams)
{
  try
  {
    // set assets
    let _table = `${DB.TABLE.CATEGORY} AS c`
    let _where: string[] = []
    let _values: ZZ = {}
    let _join: string[] = []
    const _field = query.field ? query.field.split(',') : ''
    const _tags = (query.tag && query.module) ? query.tag : ''

    // set base params
    if (query.name)
    {
      _where.push(`AND c.name LIKE '%' || $name || '%'`)
      _values['$name'] = query.name
    }
    if (query.module)
    {
      _where.push(`AND c.module LIKE $module`)
      _values['$module'] = query.module
    }
    if (query.module === helper.MODULE.NEST && query.module_srl !== undefined)
    {
      if (query.module_srl > 0)
      {
        _where.push(`AND c.module_srl = ${query.module_srl}`)
      }
      else
      {
        _where.push(`AND c.module_srl IS NULL`)
      }
    }

    // get total
    const count = helper.count({
      table: `${DB.TABLE.CATEGORY} AS c`,
      where: _where,
      values: _values,
    })
    if (count <= 0) throw new ServiceError('No data', { status: 204 })

    // get index
    let index = db.getIndex({
      table: _table,
      prefix: 'DISTINCT',
      field: _field,
      where: _where,
      join: _join,
      order: Boolean(query.order || query.sort) ? query.order : 'srl',
      sort: Boolean(query.order || query.sort) ? query.sort : 'desc',
      page: query.page,
      size: query.size,
      values: _values,
    })

    // set where for module
    _where = []
    if (query.q)
    {
      switch (query.module)
      {
        case helper.MODULE.NEST:
          _where.push(`AND (a.title LIKE "%${query.q}%" OR a.content LIKE "%${query.q}%")`)
          break
        case helper.MODULE.JSON:
          _where.push(`AND (j.name LIKE "%${query.q}%" OR j.description LIKE "%${query.q}%")`)
          break
      }
    }

    // set MOD
    const _mod: MOD = new MOD(query.mod)

    // transform items
    index.data = index.data.map((item: ZZ) => {
      if (!(item.srl && item.module)) return item
      // MOD / count
      if (_mod.check('count'))
      {
        let _itemWhere = [
          ..._where,
          `AND category_srl = ${item.srl}`,
        ]
        if (query.module && _tags) _itemWhere.push(getCountTagQuery(query.module, _tags))
        switch (item.module)
        {
          case helper.MODULE.NEST:
            item.count = articleHelper.count({
              table: `${DB.TABLE.ARTICLE} as a`,
              where: _itemWhere,
            })
            break
          case helper.MODULE.JSON:
            item.count = jsonHelper.count({
              table: `${DB.TABLE.JSON} as j`,
              where: _itemWhere,
            })
            break
        }
      }
      return item
    })

    // MOD / none
    if (_mod.check('none'))
    {
      let _newItem: ZZ = { name: 'none' }
      let _itemWhere = [ `AND category_srl IS NULL`, ..._where ]
      if (_mod.check('count'))
      {
        if (query.module && _tags) _itemWhere.push(getCountTagQuery(query.module, _tags))
        switch (query.module)
        {
          case helper.MODULE.NEST:
            if (query.module_srl) _itemWhere.push(`AND module_srl = ${query.module_srl}`)
            _newItem.count = articleHelper.count({
              table: `${DB.TABLE.ARTICLE} as a`,
              where: _itemWhere,
            })
            break
          case helper.MODULE.JSON:
            _newItem.count = jsonHelper.count({
              table: `${DB.TABLE.JSON} as j`,
              where: _itemWhere,
            })
            break
        }
      }
      index.data.push(_newItem)
    }

    // MOD / all
    if (_mod.check('all'))
    {
      let _newItem: ZZ = { name: 'all' }
      let _itemWhere = [ ..._where ]
      if (_mod.check('count'))
      {
        if (query.module && _tags) _itemWhere.push(getCountTagQuery(query.module, _tags))
        switch (query.module)
        {
          case helper.MODULE.NEST:
            if (query.module_srl) _itemWhere.push(`AND nest_srl = ${query.module_srl}`)
            _newItem.count = articleHelper.count({
              table: `${DB.TABLE.ARTICLE} as a`,
              where: _itemWhere,
            })
            break
          case helper.MODULE.JSON:
            _newItem.count = jsonHelper.count({
              table: `${DB.TABLE.JSON} as j`,
              where: _itemWhere,
            })
            break
        }
      }
      index.data.unshift(_newItem)
    }

    return {
      total: count,
      index: index.data,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Category index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}

function getCountTagQuery(module: string, tags: string): string
{
  return `AND srl IN (SELECT mt.module_srl FROM ${DB.TABLE.MAP_TAG} AS mt WHERE mt.module LIKE \'${module}\' AND mt.tag_srl IN (${tags}))`
}
