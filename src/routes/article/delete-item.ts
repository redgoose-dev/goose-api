import { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as helper from './__helper'

export default async function deleteItem(srl: number)
{
  let _transaction = false
  try
  {
    // check data
    const count = helper.count({
      where: `AND srl = ${srl}`,
    })
    if (count <= 0) throw new ServiceError('No data.', { status: 204 })

    // begin transaction
    _transaction = db.transaction('begin')

    // delete data
    // TODO: 덜 끝났음
    await helper.remove(srl)

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to delete Article.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
