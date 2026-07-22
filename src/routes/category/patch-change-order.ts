import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as helper from './__helper'
import { type CategoryModel } from './__model'

type PatchItemParams = {
  body: CategoryModel['patchChangeOrderBody'],
}

export default async function patchChangeOrder({ body }: PatchItemParams)
{
  let _transaction = false
  try
  {
    // check module
    helper.checkingModule(body.module, body.module_srl)

    // begin transaction
    _transaction = db.transaction('begin')

    // set srls
    const _srls = body.srls.split(',')

    // update data
    for (let i = 0; i < _srls.length; i++)
    {
      db.editData({
        table: DB.TABLE.CATEGORY,
        where: `srl = ${_srls[i]}`,
        set: [ 'turn = $turn' ],
        values: { '$turn': i + 1 },
      })
    }

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to change order Category.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
