import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import { parseJSON } from '@/libs/objects'
import { MODULE as MODULE_TAG } from '@/routes/tag/__helper'
import type { JsonModel } from './__model'

type GetIndexParams = {
  query: JsonModel['getIndexQuery']
}

export default async function getIndex({ query }: GetIndexParams)
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
    if (query.category)
    {
      _where.push(`AND category_srl = $category_srl`)
      _values['$category_srl'] = query.category
    }
    if (query.name)
    {
      _where.push(`AND name LIKE '%' || $name || '%'`)
      _values['$name'] = query.name
    }
    if (query.tag)
    {
      const _tags = query.tag.split(',').join(',')
      _where.push(`AND j.srl IN (SELECT mt.module_srl FROM ${DB.TABLE.MAP_TAG} AS mt WHERE mt.module LIKE $tag_module AND mt.tag_srl IN (${_tags}))`)
      _values['$tag_module'] = MODULE_TAG.JSON
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
      page: query.page,
      size: query.size,
      values: _values,
    })

    // set MOD
    const _mod: MOD = new MOD(query.mod)

    // 인덱스 데이터 컨버팅
    index.data = index.data.map((o: ZZ) => {
      // MOD / category
      if (_mod.check('category'))
      {
        // TODO: 분류 데이터가 쌓이면 만들자
        console.log('MOD: category')
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
    throw new ServiceError('Failed to get JSON index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
