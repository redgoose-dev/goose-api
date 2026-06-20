import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as messages from '@/libs/messages'
import { parseJSON, filteringObject } from '@/libs/objects'
import * as categoryHelper from '@/routes/category/__helper'
import * as tagHelper from '@/routes/tag/__helper'
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
      const _count = categoryHelper.count({
        where: [
          `AND srl = ${body.category}`,
          `AND module LIKE \'${categoryHelper.MODULE.JSON}\'`,
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
      _ready['tag'] = body.tag.split(',') || []
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

    // update tag
    if (_ready.tag !== undefined)
    {
      tagHelper.update({
        module: tagHelper.MODULE.JSON,
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

    throw new ServiceError('Failed to edit JSON.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
