import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { compareIndex } from '@/libs/objects'
import { MODULE } from './assets'
import { TagModel } from './model'
import type { BaseModel } from '@/libs/models'

export abstract class TagTool {
  static count(op: BaseModel['paramsTableSelect']): number
  {
    const count = db.getCount({
      table: DB.TABLE.MAP_TAG,
      where: op.where || '',
      values: op.values || {},
    })
    return count.data || 0
  }
  static getOriginTableName(module: string): string
  {
    switch (module)
    {
      case MODULE.ARTICLE:
        return DB.TABLE.ARTICLE
      case MODULE.CHECKLIST:
        return DB.TABLE.CHECKLIST
      case MODULE.JSON:
        return DB.TABLE.JSON
      default:
        return ''
    }
  }
  static add({ module, module_srl, tag }: ZZ)
  {
    // get tag data
    const _tag = db.getData({
      table: DB.TABLE.TAG,
      field: 'srl,name',
      where: `name GLOB \'${tag}\'`,
    })
    let _tagSrl = _tag.data?.srl as number
    // add tag data
    if (!_tagSrl)
    {
      const _tag = db.addData({
        table: DB.TABLE.TAG,
        values: [{ key: 'name', value: tag }],
      })
      _tagSrl = _tag.data as number
    }
    if (!_tagSrl) return
    // add mapping tag data
    const _count = db.getCount({
      table: DB.TABLE.MAP_TAG,
      where: [
        `AND module LIKE \'${module}\'`,
        `AND module_srl = ${module_srl}`,
        `AND tag_srl = ${_tagSrl}`,
      ],
    })
    if (_count.data <= 0)
    {
      const _added = db.addData({
        table: DB.TABLE.MAP_TAG,
        values: [
          { key: 'tag_srl', value: _tagSrl },
          { key: 'module', value: module },
          { key: 'module_srl', value: module_srl },
        ],
      })
      return _added.data
    }
  }
  static update({ module, module_srl, tags }: ZZ)
  {
    // get old tags
    const oldTags = db.getIndex({
      table: `${DB.TABLE.TAG} as t`,
      field: `t.name`,
      join: `JOIN ${DB.TABLE.MAP_TAG} as mt ON mt.tag_srl = t.srl`,
      where: [
        `AND module LIKE \'${module}\'`,
        `AND module_srl = ${module_srl}`,
      ],
    }).data?.map((o: ZZ) => (o.name)) || []
    // compare tags
    const compare = compareIndex(oldTags, tags)
    // remove tags
    if (compare.removed.length > 0)
    {
      for (const tag of compare.removed)
      {
        this.deleteTag({ module, module_srl, tag })
      }
    }
    // add tags
    if (compare.added.length > 0)
    {
      for (const tag of compare.added)
      {
        this.add({ module, module_srl, tag })
      }
    }
  }
  static delete({ module, module_srl }: ZZ)
  {
    // get mapping data
    const _mapTagSrls = db.getIndex({
      table: DB.TABLE.MAP_TAG,
      field: 'tag_srl',
      where: [
        `AND module LIKE \'${module}\'`,
        `AND module_srl = ${module_srl}`,
      ],
    }).data?.map(({ tag_srl }: ZZ) => (tag_srl))
    // delete mapping data
    db.deleteData({
      table: DB.TABLE.MAP_TAG,
      where: [
        `AND module LIKE \'${module}\'`,
        `AND module_srl = ${module_srl}`,
      ],
    })
    // delete tag data
    for (const _srl of _mapTagSrls)
    {
      const _count = db.getCount({
        table: DB.TABLE.MAP_TAG,
        where: `tag_srl = ${_srl}`,
      })
      if (_count.data <= 0)
      {
        db.deleteData({
          table: DB.TABLE.TAG,
          where: `srl = ${_srl}`,
        })
      }
    }
  }
  static deleteTag({ module, module_srl, tag }: ZZ)
  {
    // get tag srl
    const _tag = db.getData({
      table: DB.TABLE.TAG,
      field: 'srl',
      where: `name GLOB \'${tag}\'`,
    })
    const _tagSrl = _tag.data?.srl as number
    if (!_tagSrl) return
    // delete data from mapping table
    db.deleteData({
      table: DB.TABLE.MAP_TAG,
      where: [
        `AND module LIKE \'${module}\'`,
        `AND module_srl = ${module_srl}`,
        `AND tag_srl = ${_tagSrl}`,
      ],
    })
    // get count data from mapping table
    const _count = db.getCount({
      table: DB.TABLE.MAP_TAG,
      where: `tag_srl = ${_tagSrl}`,
    })
    // delete data from tag table
    if (_count.data <= 0)
    {
      db.deleteData({
        table: DB.TABLE.TAG,
        where: `srl = ${_tagSrl}`,
      })
    }
  }
}

export abstract class Tag {

  static async getIndex(query: TagModel['getIndexQuery'])
  {
    try
    {
      // set assets
      let _field = []
      let _where: string[] = []
      let _join: string[] = []
      let _values: ZZ = {}
      if (query.name)
      {
        _where.push(`AND t.name LIKE \'%${query.name}%\'`)
      }
      if (query.module)
      {
        _field.push(`t.*`)
        _join.push(`JOIN ${DB.TABLE.MAP_TAG} AS mt ON mt.tag_srl = t.srl`)
        _where.push(`AND mt.module LIKE \'${query.module}\'`)
      }
      if (query.module && query.module_srl)
      {
        _where.push(`AND mt.module_srl = ${query.module_srl}`)
      }
      // get count
      const count = db.getCount({
        table: `${DB.TABLE.TAG} AS t`,
        field: `COUNT(DISTINCT t.srl) AS count`,
        where: _where,
        join: _join,
        values: _values,
      })
      if (count.data <= 0) throw new ServiceError('No data', { status: 204 })
      // get index
      const index = db.getIndex({
        table: `${DB.TABLE.TAG} AS t`,
        field: _field,
        where: _where,
        join: _join,
        order: Boolean(query.order || query.sort) ? query.order : 'srl',
        sort: Boolean(query.order || query.sort) ? query.sort : 'desc',
        page: query.page,
        size: query.size,
        values: _values,
        debug: true
      })
      return {
        total: count.data,
        index: index.data,
      }
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed to get Tag index.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async patchItem(body: TagModel['patchItemBody'])
  {
    let _transaction = false
    try
    {
      // 원본 데이터가 존재하는지 검사
      const count = db.getCount({
        table: TagTool.getOriginTableName(body.module as string),
        where: `srl = ${body.module_srl}`,
      })
      if (count.data <= 0)
      {
        throw new ServiceError('Module data not found.', { status: 400 })
      }
      // begin transaction
      _transaction = db.transaction('begin')
      // update tag
      TagTool.update({
        module: body.module,
        module_srl: body.module_srl,
        tags: body.tags?.split(',') || [],
      })
      // commit transaction
      _transaction = db.transaction('commit')
    }
    catch (_e: any)
    {
      // collback transaction
      db.transaction('rollback', _transaction)
      throw new ServiceError('Failed to update Tag.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async deleteItem(body: TagModel['deleteItemBody'])
  {
    let _transaction = false
    try
    {
      // 원본 데이터가 존재하는지 검사
      const count = db.getCount({
        table: TagTool.getOriginTableName(body.module as string),
        where: `srl = ${body.module_srl}`,
      })
      if (count.data <= 0)
      {
        throw new ServiceError('Module data not found.', { status: 400 })
      }
      // begin transaction
      _transaction = db.transaction('begin')
      // delete data
      TagTool.delete({
        module: body.module,
        module_srl: body.module_srl,
      })
      // commit transaction
      _transaction = db.transaction('commit')
    }
    catch (_e: any)
    {
      // collback transaction
      db.transaction('rollback', _transaction)
      throw new ServiceError('Failed to delete Tag.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

}
