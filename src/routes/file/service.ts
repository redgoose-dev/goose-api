import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { BaseModel } from '@/libs/models'

export abstract class FileTool {
  static count(op: BaseModel['paramsTableSelect']): number
  {
    const count = db.getCount({
      table: DB.TABLE.FILE,
      where: op.where || '',
      values: op.values || {},
    })
    return count.data || 0
  }
}
