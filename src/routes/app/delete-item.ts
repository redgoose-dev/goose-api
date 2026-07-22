import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as helper from './__helper'

type DeleteItemParams = {
  srl?: number
  code?: string
}

export default async function deleteItem({ srl, code }: DeleteItemParams)
{
  let _transaction = false
  try
  {
    // set where
    let _where = ''
    if (srl) _where = `srl = ${srl}`
    else if (code) _where = `code LIKE \'${code}\'`

    // check exist data
    const item = db.getData({
      table: DB.TABLE.APP,
      field: 'srl',
      where: _where,
    })
    if (!item.data)
    {
      throw new ServiceError('No data', { status: 204 })
    }

    // begin transaction
    _transaction = db.transaction('begin')

    // delete app
    await helper.remove(item.data.srl)

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to delete App.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
