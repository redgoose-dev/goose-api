import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import MOD from '@/classes/MOD'
import { parseJSON } from '@/libs/objects'
import * as tagHelper from '@/routes/tag/__helper'
import type { JsonModel } from './__model'

type GetIndexParams = {
  query: JsonModel['getIndexQuery']
  service: Service
}

export default async function getIndex({ query, service }: GetIndexParams)
{
  try
  {
    // set assets
    let _table = `${DB.TABLE.JSON} AS j`
    let _where: string[] = []
    let _values: ZZ = {}
    let _join: string[] = []
    const _field = query.field ? query.field.split(',') : ''

    // set base params
    if (query.category !== undefined)
    {
      if (query.category > 0) _where.push(`AND category_srl = ${query.category}`)
      else _where.push(`AND category_srl IS NULL`)
    }
    if (query.name !== undefined)
    {
      _where.push(`AND name LIKE '%' || $name || '%'`)
      _values['$name'] = query.name
    }
    if (query.tag !== undefined)
    {
      const _tags = query.tag.split(',').join(',')
      _where.push(`AND j.srl IN (SELECT mt.module_srl FROM ${DB.TABLE.MAP_TAG} AS mt WHERE mt.module LIKE $tag_module AND mt.tag_srl IN (${_tags}))`)
      _values['$tag_module'] = tagHelper.MODULE.JSON
    }

    // get total
    const count = db.getCount({
      table: _table,
      where: _where,
      join: _join,
      values: _values,
    })
    if (count.data <= 0) throw new ServiceError('No data', { status: 204 })

    // get index data
    let index = db.getIndex({
      table: _table,
      prefix: 'DISTINCT',
      field: _field,
      where: _where,
      join: _join,
      order: Boolean(query.order || query.sort) ? query.order : 'srl',
      sort: Boolean(query.order || query.sort) ? query.sort : 'desc',
      page: query.page ?? 1,
      size: query.size ?? service.preference['json.index.size'],
      values: _values,
    })

    // set MOD
    const _mod: MOD = new MOD(query.mod)

    // transform index
    index.data = index.data.map((item: ZZ) => {
      if (item.json) item.json = parseJSON(item.json)
      // MOD / category
      if (_mod.check('category'))
      {
        item.category = db.getData({
          table: DB.TABLE.CATEGORY,
          field: 'srl,name',
          where: `srl = ${item.category_srl ?? 0}`,
        }).data
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
    throw new ServiceError('Failed to get JSON index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
