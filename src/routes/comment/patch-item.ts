import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as messages from '@/libs/messages'
import { filteringObject } from '@/libs/objects'
import * as helper from './__helper'
import type { CommentModel } from './__model'

type PatchItemParams = {
  srl: number
  body: CommentModel['patchItemBody'],
}

export default async function patchItem({ srl, body }: PatchItemParams)
{
  try
  {
    // set assets

    // get item
    const item = db.getData({
      table: DB.TABLE.COMMENT,
      where: `srl = ${srl}`,
    })
    if (!item.data) throw new ServiceError('No data', { status: 204 })

    // set ready update
    let _ready: ZZ = {
      content: undefined,
      module: undefined,
      module_srl: undefined,
    }

    // setup ready update
    if (body.content)
    {
      _ready.content = body.content
    }
    if (body.module && body.module_srl)
    {
      helper.checkModule(body.module, body.module_srl)
      _ready.module = body.module
      _ready.module_srl = body.module_srl
    }

    // check update data
    _ready = filteringObject(_ready)
    if (Object.keys(_ready).length <= 0)
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }

    // update data
    db.editData({
      table: DB.TABLE.COMMENT,
      where: `srl = ${srl}`,
      set: [
        _ready.content !== undefined && 'content = $content',
        _ready.module !== undefined && 'module = $module',
        _ready.module_srl !== undefined && 'module_srl = $module_srl',
      ],
      values: {
        '$content': body.content,
        '$module': body.module,
        '$module_srl': body.module_srl,
      },
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to edit Comment.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
