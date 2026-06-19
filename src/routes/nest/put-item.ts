import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { parseJSON } from '@/libs/objects'
import type { NestModel } from './__model'

type PutItemParams = {
  body: NestModel['putItemBody'],
}

export default async function putItem({ body }: PutItemParams)
{
  try
  {
    // check parse json
    const _json = parseJSON(body.json)

    // check app
    const _checkApp = db.getCount({
      table: DB.TABLE.APP,
      where: `srl = ${body.app}`
    })
    if (_checkApp.data <= 0)
    {
      throw new ServiceError('Not found App.', { status: 400 })
    }

    // check code
    const _checkCode = db.getCount({
      table: DB.TABLE.NEST,
      where: `code LIKE \'${body.code}\'`,
    })
    if (_checkCode.data > 0)
    {
      throw new ServiceError('Exist code in Nest.', { status: 400 })
    }

    // add data
    const added = db.addData({
      table: DB.TABLE.NEST,
      values: [
        { key: 'app_srl', value: body.app },
        { key: 'code', value: body.code },
        { key: 'name', value: body.name },
        { key: 'description', value: body.description },
        { key: 'json', value: JSON.stringify(_json) || '{}' },
        { key: 'created_at', valueName: DB.DATE_TIME },
      ],
    })

    return added.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to add Nest.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
