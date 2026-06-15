import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as tagHelper from '@/routes/tag/__helper'
import * as helper from './__helper'
import type { ChecklistModel } from './__model'

type PutItemParams = {
  body: ChecklistModel['putItemBody']
}

export default async function putItem({ body }: PutItemParams)
{
  let _transaction = false
  try
  {
    // set assets
    const _content = helper.filteringContent(body.content || '')
    const _percent = helper.getPercentIntoChecks(_content)

    // begin transaction
    _transaction = db.transaction('begin')

    // add data
    const added = db.addData({
      table: DB.TABLE.CHECKLIST,
      values: [
        { key: 'content', value: _content },
        { key: 'percent', value: _percent },
        {
          key: 'created_at',
          value: body.regdate || undefined,
          valueName: body.regdate ? undefined : DB.DATE_TIME,
        },
        {
          key: 'updated_at',
          value: body.regdate || undefined,
          valueName: body.regdate ? undefined : DB.DATE_TIME,
        },
      ],
    })

    // add tags
    if (body.tag && added.data > 0)
    {
      tagHelper.update({
        module: tagHelper.MODULE.CHECKLIST,
        module_srl: added.data,
        tags: body.tag?.split(',') || [],
      })
    }

    // commit transaction
    _transaction = db.transaction('commit')

    return added.data || 0
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to add Checklist item.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
