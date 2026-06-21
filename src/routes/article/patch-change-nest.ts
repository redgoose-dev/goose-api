import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as nestHelper from '@/routes/nest/__helper'
import * as helper from './__helper'
import type { ArticleModel } from './__model'

type PatchChangeNest = {
  srl: number,
  body: ArticleModel['patchChangeNestBody'],
}

export default async function patchChangeNest({ srl, body }: PatchChangeNest)
{
  try
  {
    // get data
    const item = db.getData({
      table: DB.TABLE.ARTICLE,
      field: 'srl,nest_srl',
      where: [
        `AND srl = ${srl}`,
        `AND mode NOT LIKE \'${helper.STATUS.READY}\'`,
      ],
    })
    if (!item.data) throw new ServiceError('No data', { status: 204 })

    // check app_srl or nest_srl
    if (!body.nest) throw new ServiceError('Not found Nest srl.', { status: 400 })

    if (body.nest && item.data.nest_srl === body.nest)
    {
      throw new ServiceError('nest_srl values are identical.', { status: 400 })
    }

    // check nest
    const _count = nestHelper.count({
      where: `srl = ${body.nest}`,
    })
    if (_count <= 0) throw new ServiceError('Not found Nest.', { status: 400 })

    // update data
    db.editData({
      table: DB.TABLE.ARTICLE,
      where: `srl = ${srl}`,
      set: [ 'nest_srl = $nest_srl' ],
      values: { '$nest_srl': body.nest },
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to change App or Nest from Article.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
