import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { BaseModel } from '@/libs/models'

export const MODULE = {
  NEST: 'nest',
  JSON: 'json',
}

export function count({ table, where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: table || DB.TABLE.CATEGORY,
    where: where || '',
    values: values || {},
  })
  return count.data || 0
}

export function getItem(srl: number, field?: string)
{
  const item = db.getData({
    table: DB.TABLE.CATEGORY,
    where: `srl = ${srl}`,
    field: field || '*',
  })
  return item.data
}

export function checkingModule(module: string, moduleSrl?: number)
{
  switch (module)
  {
    case MODULE.NEST:
      if (moduleSrl && moduleSrl > 0)
      {
        const count = db.getCount({
          table: DB.TABLE.NEST,
          where: moduleSrl ? `srl = ${moduleSrl}` : `srl IS NULL`,
        })
        if (count.data <= 0)
        {
          throw new ServiceError('Module not found.', { status: 400 })
        }
      }
      break
    case MODULE.JSON:
      break
    default:
      throw new ServiceError('Module not found.', { status: 400 })
  }
}

type CheckingExistName = {
  name: string,
  module: string,
  moduleSrl?: number
}
export function checkingExistName({ name, module, moduleSrl }: CheckingExistName)
{
  let _where = [
    `AND name LIKE \'${name}\'`,
    `AND module LIKE \'${module}\'`,
  ]
  if (module === MODULE.NEST && (moduleSrl ?? 0) > 0)
  {
    _where.push(`AND module_srl = ${moduleSrl}`)
  }
  const _count = db.getCount({
    table: DB.TABLE.CATEGORY,
    where: _where,
  })
  if (_count.data > 0)
  {
    throw new ServiceError('Exist name in Category.', { status: 400 })
  }
}

export async function remove(module: string, moduleSrl: number)
{
  console.log('Category.helper.remove()', module, moduleSrl)
  // TODO
}
