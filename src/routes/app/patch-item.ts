import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as messages from '@/libs/messages'
import { filteringObject } from '@/libs/objects'
import type { AppModel } from './__model'

type PatchItemParams = {
  srl?: number
  code?: string
  body: AppModel['patchItemBody']
}

export default async function patchItem({ srl, code, body }: PatchItemParams)
{
  try
  {
    // set where
    let _where = ''
    if (srl) _where = `srl = ${srl}`
    else if (code) _where = `code LIKE \'${code}\'`

    // get item
    const item = db.getData({
      table: DB.TABLE.APP,
      where: _where,
    })
    if (!item.data)
    {
      throw new ServiceError('App data not found.', { status: 204 })
    }

    // set ready update
    let _ready: ZZ = {
      code: undefined,
      name: undefined,
      description: undefined,
    }
    if (body.code !== undefined) _ready['code'] = body.code
    if (body.name !== undefined) _ready['name'] = body.name
    if (body.description !== undefined) _ready['description'] = body.description

    // check exist code
    if (_ready['code'])
    {
      const _count = db.getCount({
        table: DB.TABLE.APP,
        where: [
          `AND srl != ${srl}`,
          `AND code LIKE \'${_ready['code']}\'`,
        ],
      })
      if (_count.data > 0)
      {
        throw new ServiceError(`"code" already exists.`, { status: 400 })
      }
    }

    // update data
    _ready = filteringObject(_ready)
    if (Object.keys(_ready).length <= 0)
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }
    db.editData({
      table: DB.TABLE.APP,
      where: _where,
      set: [
        _ready.code !== undefined && 'code = $code',
        _ready.name !== undefined && 'name = $name',
        _ready.description !== undefined && 'description = $description',
      ],
      values: {
        '$code': _ready.code,
        '$name': _ready.name,
        '$description': _ready.description,
      },
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to edit App.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
