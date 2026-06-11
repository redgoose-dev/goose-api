import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { BaseModel } from '@/libs/models'

export const MODULE = {
  NEST: 'nest',
  JSON: 'json',
}

export abstract class CategoryTool {
  static count(op: BaseModel['paramsTableSelect']): number
  {
    const count = db.getCount({
      table: DB.TABLE.CATEGORY,
      where: op.where || '',
      values: op.values || {},
    })
    return count.data || 0
  }
}

export abstract class Category {}
