import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import { TagTool, MODULE as MODULE_TAG } from '../tag/service'
import { filteringObject } from '@/libs/objects'
import * as messages from '@/libs/messages'
import { type BaseModel } from '@/libs/models'
import { type ChecklistModel } from './model'

type GetIndexParams = {
  query: ChecklistModel['getIndexQuery']
}
type GetItemParams = {
  srl: number
  query: ChecklistModel['getItemQuery']
}
type PutItemParams = {
  body: ChecklistModel['putItemBody']
}
type PatchItemParams = {
  srl: number
  body: ChecklistModel['patchItemBody']
}

export abstract class ChecklistTool {
  static filteringContent(str: string): string
  {
    return str.replace(/'/g, "\\'")
  }
  static getPercentIntoChecks(body: string): number
  {
    if (!body) return 0
    const total = (body.match(/- \[x\]|- \[ \]/g) ?? []).length
    const checked = (body.match(/- \[x\]/g) ?? []).length
    if (!(total > 0 && checked > 0)) return 0
    return Math.floor((checked / total) * 100)
  }
  static count(op: BaseModel['paramsTableSelect'])
  {
    const count = db.getCount({
      table: DB.TABLE.CHECKLIST,
      where: op.where || '',
      values: op.values || {},
    })
    return count.data || 0
  }
}

export abstract class Checklist {

  static async getIndex({ query }: GetIndexParams)
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
        _values['$tag_module'] = MODULE_TAG.CHECKLIST
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
              '$module': MODULE_TAG.CHECKLIST,
              '$module_srl': o.srl,
            },
          })
          o.tag = _index.data?.length > 0 ? _index.data : []
        }
        return o
      })
      // return
      return {
        total: count.data,
        index: index.data,
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

  static async getItem({ srl, query }: GetItemParams)
  {
    try
    {
      // set field
      const _field = query.field ? query.field.split(',') : ''
      // get data
      const item = db.getData({
        table: DB.TABLE.CHECKLIST,
        field: _field,
        where: `srl = ${srl}`,
      })
      if (!item.data) throw new ServiceError('No data', { status: 204 })
      // set mod
      const _mod: MOD = new MOD(query.mod)
      // MOD / count-file
      if (_mod.check('count-file'))
      {}
      // MOD / tag
      if (_mod.check('tag'))
      {}
      // return
      return {
        ...item.data,
      }
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed to get Checklist item.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async putItem({ body }: PutItemParams)
  {
    let _transaction = false
    try
    {
      // set content
      const _content = ChecklistTool.filteringContent(body.content || '')
      // set percent
      const _percent = ChecklistTool.getPercentIntoChecks(_content)
      // begin transaction
      _transaction = db.transaction('begin')
      // add data
      const added = db.addData({
        table: DB.TABLE.CHECKLIST,
        values: [
          { key: 'content', value: _content },
          { key: 'percent', value: _percent },
          {
            key: 'created_at',
            value: body.regdate || undefined,
            valueName: body.regdate ? undefined : DB.DATE_TIME,
          },
          {
            key: 'updated_at',
            value: body.regdate || undefined,
            valueName: body.regdate ? undefined : DB.DATE_TIME,
          },
        ],
      })
      // add tags
      if (body.tag && added.data > 0)
      {
        TagTool.update({
          module: MODULE_TAG.CHECKLIST,
          module_srl: added.data,
          tags: body.tag?.split(',') || [],
        })
      }
      // commit transaction
      _transaction = db.transaction('commit')
      return added.data || 0
    }
    catch (_e: any)
    {
      // collback transaction
      db.transaction('rollback', _transaction)
      throw new ServiceError('Failed to add Checklist item.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async patchItem({ srl, body }: PatchItemParams)
  {
    let _transaction = false
    try
    {
      // check exist data
      const count = ChecklistTool.count({
        where: `srl = ${srl}`,
      })
      if (count <= 0) throw new ServiceError('Not found data.', { status: 204 })
      // set ready data
      let _ready: ZZ = {
        content: undefined,
        percent: undefined,
        tag: undefined,
      }
      if (body.content !== undefined)
      {
        _ready['content'] = ChecklistTool.filteringContent(body.content || '')
        _ready['percent'] = ChecklistTool.getPercentIntoChecks(_ready['content'])
      }
      if (body.tag !== undefined)
      {
        _ready['tag'] = body.tag.split(',').filter(Boolean) ?? []
      }
      // check update data
      _ready = filteringObject(_ready)
      if (Object.keys(_ready).length <= 0)
      {
        throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
      }
      // begin transaction
      _transaction = db.transaction('begin')
      // update data
      db.editData({
        table: DB.TABLE.CHECKLIST,
        where: `srl = ${srl}`,
        set: [
          _ready.content !== undefined && 'content = $content',
          _ready.percent !== undefined && 'percent = $percent',
          `updated_at = ${DB.DATE_TIME}`,
        ],
        values: {
          '$content': _ready['content'],
          '$percent': _ready['percent'],
        },
      })
      // update tags
      if (_ready.tag !== undefined)
      {
        TagTool.update({
          module: MODULE_TAG.CHECKLIST,
          module_srl: srl,
          tags: _ready.tag,
        })
      }
      // commit transaction
      _transaction = db.transaction('commit')
    }
    catch (_e: any)
    {
      // collback transaction
      db.transaction('rollback', _transaction)
      throw new ServiceError('Failed to update Checklist item.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async deleteItem(srl: number)
  {
    let _transaction = false
    try
    {
      // check data
      const count = ChecklistTool.count({
        where: `srl = ${srl}`,
      })
      if (count <= 0) throw new ServiceError('Not found data.', { status: 204 })
      // begin transaction
      _transaction = db.transaction('begin')
      // delete data
      db.deleteData({
        table: DB.TABLE.CHECKLIST,
        where: `srl = ${srl}`,
      })
      // delete tag
      TagTool.delete({
        module: MODULE_TAG.CHECKLIST,
        module_srl: srl,
      })
      // commit transaction
      _transaction = db.transaction('commit')
    }
    catch (_e: any)
    {
      console.error(_e)
      // collback transaction
      db.transaction('rollback', _transaction)
      throw new ServiceError('Failed to delete Checklist item.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

}
