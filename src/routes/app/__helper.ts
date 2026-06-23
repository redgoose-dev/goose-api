import DB, { db } from '@/classes/DB'
import * as nestHelper from '@/routes/nest/__helper'
import type { BaseModel } from '@/libs/models'

export function count({ table, where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: table ?? DB.TABLE.APP,
    where: where ?? '',
    values: values ?? {},
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
  for (const o of nests.data)
  {
    await nestHelper.remove(o.srl)
  }
  // delete app data
  db.deleteData({
    table: DB.TABLE.APP,
    where: `srl = ${srl}`,
  })
}

export function countArticle(app_srl: number)
{
  const _count = db.getCount({
    table: `${DB.TABLE.ARTICLE} as a`,
    field: 'COUNT(a.srl) AS count',
    join: `INNER JOIN ${DB.TABLE.NEST} AS n ON a.nest_srl = n.srl`,
    where: `n.app_srl = ${app_srl}`,
  })
  return _count.data ?? 0
}
