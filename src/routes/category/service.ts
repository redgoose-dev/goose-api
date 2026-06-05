import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
// import { type AuthModel } from './model'

export abstract class Category {

  /**
   * 카테고리 존재 여부 체크
   */
  static checkCount(module: string, srl: number): boolean
  {
    const count = db.getCount({
      table: DB.TABLE.CATEGORY,
      where: [
        `AND module LIKE $module`,
        `AND srl = $srl`,
      ],
      values: {
        '$module': module,
        '$srl': srl,
      },
    })
    return count.data > 0
  }

}

