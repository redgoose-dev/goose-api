/**
 * Service || App
 */

import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import * as messages from '@/libs/messages'
import { printf } from '@/libs/strings'
import type { AppModel } from './model'

type GetIndexParams = {
  foo?: string
} & AppModel['getIndexQuery']
type GetItemParams = {
  srl?: number
  code?: string
  field?: string
  mod?: string
}
type PutItemParams = {
  request: Request
  body: AppModel['putItemBody']
}

export abstract class App {

  static async getIndex(op: GetIndexParams)
  {
    // set where, values
    let _where = []
    let _values: ZZ = {}
    if (op.code)
    {
      _where.push(`AND code LIKE $code`)
      _values['$code'] = op.code
    }
    if (op.name)
    {
      _where.push(`AND name LIKE '%' || $name || '%'`)
      _values['$name'] = op.name
    }
    // get total
    const total = db.getCount({
      table: DB.TABLE.APP,
      where: _where,
      values: _values,
    })
    if (!(total.data as number > 0))
    {
      throw new ServiceError('No data', { status: 204 })
    }
    // set field
    const _field = op.field ? op.field.split(',') : ''
    // get index data
    let index = db.getIndex({
      table: DB.TABLE.APP,
      field: _field,
      where: _where,
      values: _values,
      order: Boolean(op.order || op.sort) ? op.order : 'srl',
      sort: Boolean(op.order || op.sort) ? op.sort : 'desc',
    })
    // set mod
    const _mod: MOD = new MOD(op.mod)
    // MOD / count-nest
    if (_mod.check('count-nest'))
    {
      // TODO: Nest 데이터 쌓이면 만들자
    }
    // MOD / count-article
    if (_mod.check('count-article'))
    {
      // TODO: Article 데이터 쌓이면 만들자
    }
    return {
      total: total.data,
      index: index.data,
    }
  }

  static async getItem({ srl, code, field, mod }: GetItemParams)
  {
    try
    {
      // set field
      const _field = field ? field.split(',') : ''
      // set where
      let _where = ''
      if (srl) _where = `srl = ${srl}`
      else if (code) _where = `code LIKE \'${code}\'`
      // get item
      const item = db.getData({
        table: DB.TABLE.APP,
        field: _field,
        where: _where,
      })
      if (!item.data)
      {
        throw new ServiceError('App data not found.', { status: 204 })
      }
      // set data
      let data: ZZ = {
        ...(item ? item.data : {}) as ZZ,
      }
      // set mod
      const _mod: MOD = new MOD(mod)
      // MOD / count-nest
      if (_mod.check('count-nest'))
      {
        // TODO: Nest 데이터 쌓이면 만들자
        console.log('MOD: count-nest')
      }
      // MOD / count-article
      if (_mod.check('count-article'))
      {
        // TODO: Article 데이터 쌓이면 만들자
        console.log('MOD: count-article')
      }
      return data
    }
    catch (_e: any) {
      throw new ServiceError('Failed to get App.', {
        status: _e.status,
        text: _e.message,
        err: _e,
      })
    }
  }

  static async putItem({ body }: PutItemParams)
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
