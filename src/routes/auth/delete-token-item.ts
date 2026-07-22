import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'

export default async function deleteTokenItem(srl: number)
{
  try
  {
    // get item
    const item = db.getData({
      table: DB.TABLE.TOKEN,
      where: `srl = ${srl} AND expires IS NULL`,
    })
    if (!item.data)
    {
      throw new ServiceError('Token not found.', { status: 204 })
    }

    // expires to 0
    db.editData({
      table: DB.TABLE.TOKEN,
      where: `srl = ${srl}`,
      set: [ `expires = $expires` ],
      values: { '$expires': 0 },
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed update public token.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
