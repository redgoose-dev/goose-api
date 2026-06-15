import DB, { db } from '@/classes/DB'
import type { BaseModel } from '@/libs/models'

export const MODULE = {
  NEST: 'nest',
  JSON: 'json',
}

export function count(op: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: DB.TABLE.CATEGORY,
    where: op.where || '',
    values: op.values || {},
  })
  return count.data || 0
}
