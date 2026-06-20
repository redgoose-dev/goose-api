import DB, { db } from '@/classes/DB'
import * as categoryHelper from '@/routes/category/__helper'
import * as articleHelper from '@/routes/article/__helper'
import type { BaseModel } from '@/libs/models'

export function count(op: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: DB.TABLE.NEST,
    where: op.where || '',
    values: op.values || {},
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
  // delete article
  // TODO: 아티클 목록을 가져온다. 그 목록으로 아티클 하나씩 삭제한다.
  for await (const n of [1,2,3])
  {
    await articleHelper.remove(n) // TODO: 작업예정
  }
  // delete category
  await categoryHelper.remove('module', 0) // TODO: 작업예정
  // delete nest
  db.deleteData({
    table: DB.TABLE.NEST,
    where: `srl = ${srl}`,
  })
  console.log('Nest.helper.remove()', srl)
}
