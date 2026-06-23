import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as helper from './__helper'
import * as articleHelper from '@/routes/article/__helper'
import type { CommentModel } from './__model'

type PutItemParams = {
  body: CommentModel['putItemBody'],
}

export default async function putItem({ body }: PutItemParams)
{
  try
  {
    // check exist module item
    switch (body.module)
    {
      case helper.MODULE.ARTICLE:
        const _count = articleHelper.count({
          where: `srl = ${body.module_srl}`,
        })
        if (_count <= 0) throw new ServiceError('Module data not found.', { status: 400 })
        break
      default:
        throw new ServiceError('Invalid module name.', { status: 400 })
    }

    // add data
    const added = db.addData({
      table: DB.TABLE.COMMENT,
      values: [
        { key: 'content', value: body.content },
        { key: 'module', value: body.module },
        { key: 'module_srl', value: body.module_srl },
        { key: 'created_at', valueName: DB.DATE_TIME },
        { key: 'updated_at', valueName: DB.DATE_TIME },
      ],
    })

    return added.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to add Comment.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
