import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as fileHelper from '@/routes/file/__helper'
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
    if (count <= 0) throw new ServiceError('Not found data.', { status: 204 })

    // begin transaction
    _transaction = db.transaction('begin')

    // delete data
    db.deleteData({
      table: DB.TABLE.CHECKLIST,
      where: `srl = ${srl}`,
    })

    // delete file
    await fileHelper.remove({
      module: fileHelper.MODULE.CHECKLIST,
      module_srl: srl,
    })

    // delete tag
    tagHelper.remove({
      module: tagHelper.MODULE.CHECKLIST,
      module_srl: srl,
    })

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to delete Checklist.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
