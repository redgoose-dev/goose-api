import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { TagModel } from './__model'
import * as helper from './__helper'

type PatchItemParams = {
  body: TagModel['patchItemBody']
}

export default async function patchItem({ body }: PatchItemParams)
{
  let _transaction = false
  try
  {
    // 원본 데이터가 존재하는지 검사
    const count = db.getCount({
      table: helper.getOriginTableName(body.module as string),
      where: `srl = ${body.module_srl}`,
    })
    if (count.data <= 0)
    {
      throw new ServiceError('Module data not found.', { status: 400 })
    }

    // begin transaction
    _transaction = db.transaction('begin')

    // update tag
    helper.update({
      module: body.module,
      module_srl: body.module_srl,
      tags: body.tags?.split(',') || [],
    })

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to update Tag.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
