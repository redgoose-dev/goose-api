/**
 * Service || App
 */

import DB, { db } from '@/classes/DB'
import type { AppModel } from './model'
import ServiceError from '@/classes/ServiceError'
import * as messages from '@/libs/messages'
import { printf } from '@/libs/strings'

type PutItemParams = {
  request: Request
  body: AppModel['putItemBody']
}

export abstract class App {

  static async getIndex()
  {
    return {
      message: `GET /app/`,
    }
  }

  static async getItem(srl: number)
  {
    return {
      message: `GET /app/:srl/`,
      path: `GET /app/${srl}/`,
    }
  }

  static async putItem({ body, request }: PutItemParams)
  {
    try
    {
      // check exist data
      const appCount = db.getCount({
        table: DB.TABLE.APP,
        where: 'code = $code',
        values: { '$code': body.code },
      })
      if ((appCount.data as number) > 0)
      {
        throw new ServiceError(printf(messages.ERR_EXISTS, 'code'), { status: 400 })
      }
      // add data
      const addedData = db.addData({
        table: DB.TABLE.APP,
        values: [
          { key: 'code', value: body.code },
          { key: 'name', value: body.name },
          body.description && { key: 'description', value: body.description },
          { key: 'created_at', valueName: DB.DATE_TIME },
        ].filter(Boolean),
      })
      if (!addedData.data)
      {
        throw new Error(messages.DB_FAIL_PUT_DATA)
      }
      return addedData.data || 0
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed to add App.', {
        status: _e.status,
        text: _e.message,
        err: _e,
      })
    }
  }

  static async patchItem()
  {
    return {
      message: `PATCH /app/:srl/`,
    }
  }

  static async deleteItem()
  {
    // TODO: Article 데이터 삭제 (파일, 댓글, 태그)
    // TODO: Nest 데이터 삭제 (카테고리)
    // TODO: App 데이터 삭제
    return {
      message: `DELETE /app/:srl/`,
    }
  }

}
