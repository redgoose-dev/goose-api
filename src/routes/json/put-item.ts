import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { parseJSON } from '@/libs/objects'
import * as tagHelper from '@/routes/tag/__helper'
import * as categoryHelper from '@/routes/category/__helper'
import type { JsonModel } from './__model'

type PutItemParams = {
  body: JsonModel['putItemBody'],
}

export default async function putItem({ body }: PutItemParams)
{
  let _transaction = false
  try
  {
    // check parse json
    const jsonData = parseJSON(body.json)

    // check category
    if (body.category)
    {
      // TODO: 작동하는지 확인 필요함
      const _count = categoryHelper.count({
        where: [
          `AND srl = ${body.category}`,
          `AND module LIKE \'${categoryHelper.MODULE.JSON}\'`,
        ],
      })
      if (_count <= 0) throw new ServiceError(`Invalid category`, { status: 400 })
    }

    // begin transaction
    _transaction = db.transaction('begin')

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
      const _tags = body.tag.split(',')
      for (const tag of _tags)
      {
        tagHelper.add({
          module: tagHelper.MODULE.JSON,
          module_srl: added.data,
          tag,
        })
      }
    }

    // commit transaction
    _transaction = db.transaction('commit')

    return added.data
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to add JSON.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
