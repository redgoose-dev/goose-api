import DB, { db } from '@/classes/DB'
import type { BaseModel } from '@/libs/models'

export function count({ where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: `${DB.TABLE.JSON} AS j`,
    where: where || '',
    values: values || {},
  })
  return count.data || 0
}
