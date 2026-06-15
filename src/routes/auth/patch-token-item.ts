import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { AuthModel } from './__model'

type PatchTokenItem = {
  srl: number
  body: AuthModel['patchTokenBody']
}

export default async function patchToken({ srl, body }: PatchTokenItem)
{
  try
  {
    // get token count
    const count = db.getCount({
      table: DB.TABLE.TOKEN,
      where: `srl = ${srl} AND expires IS NULL`,
    })
    if (!(count.data > 0))
    {
      throw new ServiceError('Token not found.', { status: 204 })
    }

    // update data
    db.editData({
      table: DB.TABLE.TOKEN,
      where: `srl = ${srl}`,
      set: [ 'description = $description' ],
      values: { '$description': body.description },
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed update public Token.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
