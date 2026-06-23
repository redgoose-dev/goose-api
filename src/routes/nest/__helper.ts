import DB, { db } from '@/classes/DB'
import * as categoryHelper from '@/routes/category/__helper'
import * as articleHelper from '@/routes/article/__helper'
import type { BaseModel } from '@/libs/models'

export function count({ table, where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: table ?? DB.TABLE.NEST,
    where: where ?? '',
    values: values ?? {},
  })
  return count.data || 0
}

export function getItem(srl: number, field?: string)
{
  const item = db.getData({
    table: DB.TABLE.NEST,
    where: `srl = ${srl}`,
    field: field || '*',
  })
  return item.data
}

export async function remove(srl: number)
{
  // delete category
  await categoryHelper.remove({
    module: categoryHelper.MODULE.NEST,
    module_srl: srl,
  })
  // delete article
  const _index = db.getIndex({
    table: DB.TABLE.ARTICLE,
    field: 'srl',
    where: `nest_srl = ${srl}`,
  }).data
  for (const item of _index)
  {
    await articleHelper.remove(item.srl)
  }
  // delete nest
  db.deleteData({
    table: DB.TABLE.NEST,
    where: `srl = ${srl}`,
  })
}
