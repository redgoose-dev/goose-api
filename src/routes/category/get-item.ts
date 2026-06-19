import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { CategoryModel } from './__model'

type GetItemParams = {
  srl: number
  query: CategoryModel['getItemQuery']
}

export default async function getItem({ srl, query }: GetItemParams)
{
  try
  {
    // set assets
    let _table = `${DB.TABLE.CATEGORY} AS c`
    const _field = query.field ? query.field.split(',') : ''

    // get data
    let item = db.getData({
      table: _table,
      field: _field,
      where: `srl = ${srl}`,
    })
    if (!item.data) throw new ServiceError('No data', { status: 204 })

    return item.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Category item.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
