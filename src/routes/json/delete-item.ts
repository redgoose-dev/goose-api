import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as tagHelper from '@/routes/tag/__helper'
import * as helper from './__helper'

export default async function deleteItem(srl: number)
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

    // delete data
    db.deleteData({
      table: DB.TABLE.JSON,
      where: `srl = ${srl}`,
    })

    // delete tags
    tagHelper.remove({
      module: tagHelper.MODULE.JSON,
      module_srl: srl,
    })

    // TODO: delete files

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to delete JSON.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
