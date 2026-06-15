import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as messages from '@/libs/messages'
import { parseJSON, filteringObject } from '@/libs/objects'
import { CategoryTool, MODULE as CATEGORY_MODULE } from '@/routes/category/__helper'
import * as helper from './__helper'
import { type JsonModel } from './__model'

type PatchItemParams = {
  srl: number
  body: JsonModel['patchItemBody'],
}

export default async function patchItem({ srl, body }: PatchItemParams)
{
  let _transaction = false
  try
  {
    // check data
    const count = helper.count({
      where: `srl = ${srl}`,
    })
    if (count <= 0) throw new ServiceError('No data.', { status: 204 })

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
      // TODO: 리팩토링 필요
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
