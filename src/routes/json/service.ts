import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import { Category } from '@/routes/category/service'
import { MODULE as CATEGORY_MODULE } from '@/routes/category/assets'
// import { Tag } from '@/routes/tag/service'
import * as messages from '@/libs/messages'
import { printf } from '@/libs/strings'
import { checkExistValueInObject, parseJSON } from '@/libs/objects'
import type { JsonModel } from './model'

type PutItemParams = {
  body: JsonModel['putItemBody'],
}

export abstract class Json {

  static async getIndex()
  {}

  static async getItem()
  {}

  static async putItem({ body }: PutItemParams)
  {
    let _transction = false
    try
    {
      // check parse json
      const jsonData = parseJSON(body.json)
      // check category
      if (body.category)
      {
        // TODO: 카테고리 체크 작동검사
        if (!Category.checkCount(CATEGORY_MODULE.JSON, body.category))
        {
          throw new ServiceError(`Invalid category`, { status: 400 })
        }
      }
      _transction = db.transaction('begin')
      // add json data
      const added = db.addData({
        table: DB.TABLE.JSON,
        values: [
          { key: 'category_srl', value: body.category },
          { key: 'name', value: body.name },
          { key: 'description', value: body.description },
          { key: 'json', value: JSON.stringify(jsonData) || '{}' },
          { key: 'created_at', valueName: DB.DATE_TIME },
          { key: 'updated_at', valueName: DB.DATE_TIME },
        ],
      })
      // add tag
      if (body.tag)
      {
        // TODO: 태그 추가하기 (태그 클래스에서..)
        console.log('TODO: body.tag', body.tag)
      }
      _transction = db.transaction('commit')
      // return
      return added.data
    }
    catch (_e: any)
    {
      db.transaction('rollback', _transction)
      throw new ServiceError('Failed to add JSON.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async patchItem()
  {}

  static async deleteItem()
  {}

}
