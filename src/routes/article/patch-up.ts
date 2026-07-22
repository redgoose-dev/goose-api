import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { filteringObject } from '@/libs/objects'
import * as messages from '@/libs/messages'
import type { ArticleModel } from './__model'

type PatchUpParams = {
  srl: number
  body: ArticleModel['patchUpBody']
}

export default async function patchUp({ srl, body }: PatchUpParams)
{
  try
  {
    // get item
    const item = db.getData({
      table: DB.TABLE.ARTICLE,
      field: 'srl,hit,star',
      where: `srl = ${srl}`,
    })
    if (!item.data) throw new ServiceError('No data.', { status: 204 })

    // set ready update
    let _ready: ZZ = {
      hit: undefined,
      star: undefined,
    }

    // setup ready update
    let _count = body.count === undefined ? 1 : Number(body.count)
    switch (body.mode)
    {
      case 'hit':
        _ready.hit = `hit = hit + ${_count}`
        break
      case 'star':
        _ready.star = `star = star + ${_count}`
        break
    }

    // check update data
    _ready = filteringObject(_ready)
    if (Object.keys(_ready).length <= 0)
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }

    // update data
    db.editData({
      table: DB.TABLE.ARTICLE,
      where: `srl = ${srl}`,
      set: [
        _ready.hit !== undefined && _ready.hit,
        _ready.star !== undefined && _ready.star,
      ],
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to up HIT or STAR.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
