import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { deleteFile } from '@/libs/file'
import * as helper from './__helper'

export default async function deleteItem(srl: number)
{
  let _transaction = false
  try
  {
    // set assets
    let _where = `srl = ${srl}`

    // begin transaction
    _transaction = db.transaction('begin')

    // get item
    const item = db.getData({
      table: DB.TABLE.FILE,
      field: 'srl,code,path',
      where: _where,
    })
    if (!item.data)
    {
      throw new ServiceError('No data', { status: 204 })
    }

    // delete data
    db.deleteData({
      table: DB.TABLE.FILE,
      where: _where,
    })

    // delete file
    await deleteFile(item.data.path)

    // delete cache files
    await helper.deleteCache(item.data.code)

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to delete File.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
