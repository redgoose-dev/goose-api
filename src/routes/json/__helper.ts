import DB, { db } from '@/classes/DB'
import type { BaseModel } from '@/libs/models'

export function count({ table, where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: table ?? DB.TABLE.JSON,
    where: where ?? '',
    values: values ?? {},
  })
  return count.data || 0
}
