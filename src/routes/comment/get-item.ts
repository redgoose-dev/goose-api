import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { CommentModel } from './__model'

type GetItemParams = {
  srl: number
  query: CommentModel['getItemQuery']
}

export default async function getItem({ srl, query }: GetItemParams)
{
  try
  {
    // set assets
    const _field = query.field ? query.field.split(',') : ''

    // get data
    const item = db.getData({
      table: DB.TABLE.COMMENT,
      field: _field,
      where: `srl = ${srl}`,
    })
    if (!item.data) throw new ServiceError('No data', { status: 204 })

    return item.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Comment.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
