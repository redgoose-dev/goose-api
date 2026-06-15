import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { filteringObject } from '@/libs/objects'
import * as messages from '@/libs/messages'
import * as tagHelper from '@/routes/tag/__helper'
import * as helper from './__helper'
import type { ChecklistModel } from './__model'

type PatchItemParams = {
  srl: number
  body: ChecklistModel['patchItemBody']
}

export default async function patchItem({ srl, body }: PatchItemParams)
{
  let _transaction = false
  try
  {
    // check exist data
    const count = helper.count({
      where: `srl = ${srl}`,
    })
    if (count <= 0) throw new ServiceError('Not found data.', { status: 204 })

    // set ready data
    let _ready: ZZ = {
      content: undefined,
      percent: undefined,
      tag: undefined,
    }
    if (body.content !== undefined)
    {
      _ready['content'] = helper.filteringContent(body.content || '')
      _ready['percent'] = helper.getPercentIntoChecks(_ready['content'])
    }
    if (body.tag !== undefined)
    {
      _ready['tag'] = body.tag.split(',').filter(Boolean) ?? []
    }

    // check update data
    _ready = filteringObject(_ready)
    if (Object.keys(_ready).length <= 0)
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }

    // begin transaction
    _transaction = db.transaction('begin')

    // update data
    db.editData({
      table: DB.TABLE.CHECKLIST,
      where: `srl = ${srl}`,
      set: [
        _ready.content !== undefined && 'content = $content',
        _ready.percent !== undefined && 'percent = $percent',
        `updated_at = ${DB.DATE_TIME}`,
      ],
      values: {
        '$content': _ready['content'],
        '$percent': _ready['percent'],
      },
    })

    // update tags
    if (_ready.tag !== undefined)
    {
      tagHelper.update({
        module: tagHelper.MODULE.CHECKLIST,
        module_srl: srl,
        tags: _ready.tag,
      })
    }

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to update Checklist item.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}



