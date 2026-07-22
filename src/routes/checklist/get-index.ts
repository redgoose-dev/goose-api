import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import MOD from '@/classes/MOD'
import * as tagHelper from '@/routes/tag/__helper'
import type { ChecklistModel } from './__model'

type GetIndexParams = {
  query: ChecklistModel['getIndexQuery']
  service: Service
}

export default async function getIndex({ query, service }: GetIndexParams)
{
  try
  {
    // set assets
    const _table = `${DB.TABLE.CHECKLIST} AS c`
    const _field = query.field ? query.field.split(',') : ''
    let _where: string[] = []
    let _values: ZZ = {}
    let _join: string[] = []
    if (query.content)
    {
      _where.push(`AND content LIKE '%' || $content || '%'`)
      _values['$content'] = query.content.trim()
    }
    if (query.start && query.end)
    {
      _where.push(`AND (created_at BETWEEN $dateStart AND $dateEnd)`)
      _values['$dateStart'] = `${query.start} 00:00:00`
      _values['$dateEnd'] = `${query.end} 23:59:59`
    }
    if (query.tag)
    {
      const _tags = query.tag.split(',').join(',')
      _where.push(`AND c.srl IN (SELECT mt.module_srl FROM ${DB.TABLE.MAP_TAG} AS mt WHERE mt.module LIKE $tag_module AND mt.tag_srl in (${_tags}))`)
      _values['$tag_module'] = tagHelper.MODULE.CHECKLIST
    }

    // get count
    const count = db.getCount({
      table: _table,
      where: _where,
      join: _join,
      values: _values,
    })
    if (count.data <= 0) throw new ServiceError('No data', { status: 204 })

    // get index data
    const index = db.getIndex({
      table: _table,
      prefix: 'DISTINCT',
      field: _field,
      where: _where,
      join: _join,
      order: query.order,
      page: query.page ?? 1,
      size: query.size ?? service.preference['checklist.index.size'],
      values: _values,
    })

    // set MOD
    const _mod: MOD = new MOD(query.mod)

    // 인덱스 데이터 컨버팅
    const _index = index.data.map((o: ZZ) => {
      // MOD / tag
      if (_mod.check('tag'))
      {
        const _index = db.getIndex({
          table: `${DB.TABLE.TAG} AS t`,
          field: 't.srl,t.name',
          join: `JOIN ${DB.TABLE.MAP_TAG} AS mt ON mt.tag_srl = t.srl`,
          where: [
            'AND module LIKE $module',
            'AND module_srl = $module_srl'
          ],
          values: {
            '$module': tagHelper.MODULE.CHECKLIST,
            '$module_srl': o.srl,
          },
        })
        o.tag = _index.data?.length > 0 ? _index.data : []
      }
      return o
    })

    return {
      total: count.data,
      index: _index,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Checklist index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
