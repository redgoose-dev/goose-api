import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { ArticleModel } from './__model'
import {filteringObject} from "@/libs/objects.ts";
import * as messages from "@/libs/messages.ts";

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
    switch (body.mode)
    {
      case 'hit':
        _ready.hit = `hit = hit + 1`
        break
      case 'star':
        _ready.star = `star = star + 1`
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
    throw new ServiceError('Failed to up hit or star.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
