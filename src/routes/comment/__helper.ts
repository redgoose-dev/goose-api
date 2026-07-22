import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as fileHelper from '@/routes/file/__helper'
import type { BaseModel } from '@/libs/models'

export const MODULE = {
  ARTICLE: 'article',
}

export function count({ table, where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: table ?? DB.TABLE.COMMENT,
    where: where ?? '',
    values: values ?? {},
  })
  return count.data || 0
}

export function checkModule(module: string, moduleSrl: number)
{
  let _success = false
  switch (module)
  {
    case MODULE.ARTICLE:
      const _count = db.getCount({
        table: DB.TABLE.ARTICLE,
        where: `srl = ${moduleSrl}`,
      })
      if (_count.data > 0) _success = true
      break
  }
  if (!_success)
  {
    throw new ServiceError('module not found', { status: 400 })
  }
}

export async function remove(srl: number)
{
  // file data
  await fileHelper.remove({
    module: fileHelper.MODULE.COMMENT,
    module_srl: srl,
  })
  // comment data
  db.deleteData({
    table: DB.TABLE.COMMENT,
    where: `srl = ${srl}`,
  })
}

type RemoveWithModuleParams = {
  module: string
  module_srl: number
}
export async function removeWithModule({ module, module_srl }: RemoveWithModuleParams)
{
  switch (module)
  {
    case MODULE.ARTICLE:
      const items = db.getIndex({
        table: DB.TABLE.COMMENT,
        field: 'srl',
        where: [
          `AND module LIKE \'${module}\'`,
          `AND module_srl = ${module_srl}`,
        ],
      })
      for (const item of items.data)
      {
        await remove(item.srl)
      }
      break
    default:
      throw new ServiceError('module not found', { status: 400 })
  }
}
