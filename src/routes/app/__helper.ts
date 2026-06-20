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

export function getItemFromNest(nest_srl: number, field: string)
{
  const _field = field ? field.split(',').map(o => (`a.${o}`)).join(',') : 'a.*'
  const item = db.getData({
    table: `${DB.TABLE.APP} as a`,
    field: `DISTINCT ${_field}`,
    join: `INNER JOIN ${DB.TABLE.NEST} AS n ON n.app_srl = a.srl AND n.srl = ${nest_srl}`,
  })
  return item.data
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
