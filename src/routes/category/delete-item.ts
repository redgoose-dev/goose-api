import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as helper from './__helper'

export default async function deleteItem(srl: number)
{
  try
  {
    // check data
    const count = helper.count({
      where: `srl = ${srl}`,
    })
    if (count <= 0) throw new ServiceError('No data.', { status: 204 })

    // delete data
    db.deleteData({
      table: DB.TABLE.CATEGORY,
      where: `srl = ${srl}`,
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to delete Category.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
