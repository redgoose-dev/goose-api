import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as messages from '@/libs/messages'
import { filteringObject } from '@/libs/objects'
import * as helper from './__helper'
import { type CategoryModel } from './__model'

type PatchItemParams = {
  srl: number
  body: CategoryModel['patchItemBody'],
}

export default async function patchItem({ srl, body }: PatchItemParams)
{
  try
  {
    // check item
    const item = db.getData({
      table: DB.TABLE.CATEGORY,
      where: `srl = ${srl}`,
    })
    if (!item.data) throw new ServiceError('No data.', { status: 204 })

    // set ready update
    let _ready: ZZ = {
      name: undefined,
    }
    if (body.name)
    {
      // checking exist name
      helper.checkingExistName({
        module: item.data.module,
        moduleSrl: item.data.module_srl,
        name: body.name,
      })
      _ready.name = body.name
    }

    // check update data
    _ready = filteringObject(_ready)
    if (Object.keys(_ready).length <= 0)
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }

    // update data
    db.editData({
      table: DB.TABLE.CATEGORY,
      where: `srl = ${srl}`,
      set: [
        _ready.name !== undefined && `name = $name`,
      ],
      values: {
        '$name': _ready.name,
      },
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to edit Category.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
