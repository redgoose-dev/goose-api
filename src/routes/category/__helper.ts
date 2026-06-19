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

export async function remove(module: string, moduleSrl: number)
{
  console.log('Category.helper.remove()', module, moduleSrl)
  // TODO
}
