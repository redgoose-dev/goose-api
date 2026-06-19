import DB, { db } from '@/classes/DB'
import * as nestHelper from '@/routes/nest/__helper'
import type { BaseModel } from '@/libs/models'

export function count(op: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: DB.TABLE.APP,
    where: op.where || '',
    values: op.values || {},
  })
  return count.data || 0
}

export async function remove(srl: number)
{
  // get nests
  const nests = db.getIndex({
    table: DB.TABLE.NEST,
    field: 'srl',
    where: `app_srl = ${srl}`,
  })
  // remove nests
  for await (const o of nests.data)
  {
    await nestHelper.remove(o.srl)
  }
  // delete app data
  db.deleteData({
    table: DB.TABLE.APP,
    where: `srl = ${srl}`,
  })
}
