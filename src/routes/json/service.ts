import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import { CategoryTool, MODULE as CATEGORY_MODULE } from '@/routes/category/service'
import { TagTool, MODULE as MODULE_TAG } from '@/routes/tag/service'
import { FileTool, MODULE as FILE_MODULE } from '@/routes/file/service'
import * as messages from '@/libs/messages'
import { parseJSON, filteringObject } from '@/libs/objects'
import { type BaseModel } from '@/libs/models'
import { type JsonModel } from './model'

type GetIndexParams = {
  query: JsonModel['getIndexQuery']
}
type GetItemParams = {
  srl: number
  query: JsonModel['getItemQuery']
}
type PutItemParams = {
  body: JsonModel['putItemBody'],
}
type PatchItemParams = {
  srl: number
  body: JsonModel['patchItemBody']
}

export abstract class JsonTool {
  static count(op: BaseModel['paramsTableSelect']): number
  {
    const count = db.getCount({
      table: DB.TABLE.JSON,
      where: op.where || '',
      values: op.values || {},
    })
    return count.data || 0
  }
}

export abstract class Json {

  static async getIndex({ query }: GetIndexParams)
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
      const total = db.getCount({
        table: _table,
        where: _where,
        join: _join,
        values: _values,
      })
      if (total.data <= 0) throw new ServiceError('No data', { status: 204 })
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
      // return
      return {
        total: total.data,
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

  static async getItem({ srl, query }: GetItemParams)
  {
    try
    {
      // set assets
      let _table = `${DB.TABLE.JSON} AS j`
      const _field = query.field ? query.field.split(',') : ''
      // get data
      let item = db.getData({
        table: _table,
        field: _field,
        where: `srl = ${srl}`,
      })
      if (!item.data) throw new ServiceError('No data', { status: 204 })
      // set json data
      if (item.data.json)
      {
        item.data.json = parseJSON(item.data.json)
      }
      // set mod
      const _mod: MOD = new MOD(query.mod)
      // MOD / count-file
      if (_mod.check('count-file'))
      {
        // TODO: file 테이블 데이터가 쌓이면 만들자
        // const _count = FileTool.count({
        //   where: [
        //     `AND module = \'${FILE_MODULE.JSON}\'`,
        //     `AND module_srl = ${srl}`,
        //   ],
        // })
      }
      // return
      return item.data
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed to get JSON.', {
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
      // check parse json
      const jsonData = parseJSON(body.json)
      // check category
      if (body.category)
      {
        const _count = CategoryTool.count({
          where: [
            `AND srl = ${body.category}`,
            `AND module LIKE \'${CATEGORY_MODULE.JSON}\'`,
          ],
        })
        if (_count <= 0) throw new ServiceError(`Invalid category`, { status: 400 })
      }
      // begin transaction
      _transaction = db.transaction('begin')
      // add json data
      const added = db.addData({
        table: DB.TABLE.JSON,
        values: [
          { key: 'category_srl', value: body.category },
          { key: 'name', value: body.name },
          { key: 'description', value: body.description },
          { key: 'json', value: JSON.stringify(jsonData) || '{}' },
          { key: 'created_at', valueName: DB.DATE_TIME },
          { key: 'updated_at', valueName: DB.DATE_TIME },
        ],
      })
      // add tag
      if (body.tag)
      {
        const _tags = body.tag.split(',')
        for (const tag of _tags)
        {
          TagTool.add({
            module: MODULE_TAG.JSON,
            module_srl: added.data,
            tag,
          })
        }
      }
      // commit transaction
      _transaction = db.transaction('commit')
      // return
      return added.data
    }
    catch (_e: any)
    {
      // collback transaction
      db.transaction('rollback', _transaction)
      throw new ServiceError('Failed to add JSON.', {
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
      // check data
      const count = JsonTool.count({
        where: `srl = ${srl}`,
      })
      if (count <= 0) throw new ServiceError('Not found data.', { status: 204 })
      // begin transaction
      _transaction = db.transaction('begin')
      // set ready update
      let _ready: ZZ = {
        category_srl: undefined,
        name: undefined,
        description: undefined,
        json: undefined,
        tag: undefined,
      }
      if (body.category)
      {
        // TODO: 잘 작동하는지 확인필요
        const _count = CategoryTool.count({
          where: [
            `AND srl = ${body.category}`,
            `AND module LIKE \'${CATEGORY_MODULE.JSON}\'`,
          ],
        })
        if (_count <= 0) throw new ServiceError(`Invalid category`, { status: 400 })
        _ready['category_srl'] = body.category
      }
      else if (body.category !== undefined)
      {
        _ready['category_srl'] = null
      }
      if (body.name !== undefined) _ready['name'] = body.name
      if (body.description !== undefined) _ready['description'] = body.description
      if (body.json !== undefined)
      {
        const jsonData = parseJSON(body.json)
        _ready['json'] = JSON.stringify(jsonData) || '{}'
      }
      if (body.tag !== undefined)
      {
        // TODO: 태그 데이터 업데이트하기. 트랜잭션 영역이기 때문에 먼저 수정해도 된다.
        // TagTool.update()
        _ready['tag'] = body.tag
      }
      // check update data
      _ready = filteringObject(_ready)
      if (Object.keys(_ready).length <= 0)
      {
        throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
      }
      // update data
      db.editData({
        table: DB.TABLE.JSON,
        where: `srl = ${srl}`,
        set: [
          _ready.category_srl !== undefined && 'category_srl = $category_srl',
          _ready.name !== undefined && 'name = $name',
          _ready.description !== undefined && 'description = $description',
          _ready.json !== undefined && 'json = $json',
          `updated_at = ${DB.DATE_TIME}`,
        ],
        values: {
          '$category_srl': _ready.category_srl,
          '$name': _ready.name,
          '$description': _ready.description,
          '$json': _ready.json,
        },
      })
      // commit transaction
      _transaction = db.transaction('commit')
    }
    catch (_e: any)
    {
      // collback transaction
      db.transaction('rollback', _transaction)
      throw new ServiceError('Failed to edit JSON.', {
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
      const count = JsonTool.count({
        where: `srl = ${srl}`,
      })
      if (count <= 0) throw new ServiceError('No data.', { status: 204 })
      // begin transaction
      _transaction = db.transaction('begin')
      // delete data
      db.deleteData({
        table: DB.TABLE.JSON,
        where: `srl = ${srl}`,
      })
      // delete tags
      TagTool.delete({
        module: MODULE_TAG.JSON,
        module_srl: srl,
      })
      // TODO: delete files
      // commit transaction
      _transaction = db.transaction('commit')
    }
    catch (_e: any)
    {
      console.error(_e)
      // collback transaction
      db.transaction('rollback', _transaction)
      throw new ServiceError('Failed to delete JSON.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

}
