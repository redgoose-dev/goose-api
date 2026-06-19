import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as messages from '@/libs/messages'
import { parseJSON, filteringObject } from '@/libs/objects'
import * as appHelper from '@/routes/app/__helper'
import * as helper from './__helper'
import type { NestModel } from './__model'

type PatchItemParams = {
  srl: number
  body: NestModel['patchItemBody']
}

export default async function patchItem({ srl, body }: PatchItemParams)
{
  try
  {
    // set assets
    const _table = `${DB.TABLE.NEST} AS n`

    // get data
    const item = db.getData({
      table: _table,
      where: `srl = ${srl}`,
    })
    if (!item.data) throw new ServiceError('No data.', { status: 204 })

    // check app_srl
    if (body.app)
    {
      const _count = appHelper.count({
        where: `srl = ${body.app}`,
      })
      if (_count <= 0)
      {
        throw new ServiceError('Invalid app.', { status: 400 })
      }
    }

    // set ready update
    let _ready: ZZ = {
      app_srl: undefined,
      code: undefined,
      name: undefined,
      description: undefined,
      json: undefined,
    }

    // check exist code
    if (body.app)
    {
      _ready.app_srl = body.app
    }
    if (body.code && item.data.code !== body.code)
    {
      _ready.code = body.code
      const _count = helper.count({
        where: `code LIKE \'${body.code}\'`,
      })
      if (_count > 0)
      {
        throw new ServiceError('"code" already exists.', { status: 400 })
      }
    }
    if (body.name)
    {
      _ready.name = body.name
    }
    if (body.description)
    {
      _ready.description = body.description
    }
    if (body.json)
    {
      const _json = parseJSON(body.json)
      if (!_json) throw new ServiceError('Invalid JSON data.', { status: 400 })
      _ready.json = JSON.stringify(_json)
    }

    // check update data
    _ready = filteringObject(_ready)
    if (Object.keys(_ready).length <= 0)
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }

    // update data
    db.editData({
      table: DB.TABLE.NEST,
      where: `srl = ${srl}`,
      set: [
        _ready.app_srl !== undefined && 'app_srl = $app_srl',
        _ready.code !== undefined && 'code = $code',
        _ready.name !== undefined && 'name = $name',
        _ready.description !== undefined && 'description = $description',
        _ready.json !== undefined && 'json = $json',
      ],
      values: {
        $app_srl: _ready.app_srl,
        $code: _ready.code,
        $name: _ready.name,
        $description: _ready.description,
        $json: _ready.json,
      },
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to edit Nest.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
