import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as messages from '@/libs/messages'
import { printf } from '@/libs/strings'
import type { AppModel } from './__model'

type PutItemParams = {
  body: AppModel['putItemBody']
}

export default async function putItem({ body }: PutItemParams)
{
  try
  {
    // check exist data
    const count = db.getCount({
      table: DB.TABLE.APP,
      where: 'code = $code',
      values: { '$code': body.code },
    })
    if ((count.data as number) > 0)
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

    return addedData.data || 0
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to add App.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
