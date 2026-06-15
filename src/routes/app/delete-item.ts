import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'

type DeleteItemParams = {
  srl?: number
  code?: string
}

export default async function deleteItem({ srl, code }: DeleteItemParams)
{
  let _transaction = false
  try
  {
    // set where
    let _where = ''
    if (srl) _where = `srl = ${srl}`
    else if (code) _where = `code LIKE \'${code}\'`

    // check exist data
    const _count = db.getCount({
      table: DB.TABLE.APP,
      where: _where,
    })
    if (!(_count.data > 0))
    {
      throw new ServiceError('App data not found.', { status: 204 })
    }

    // begin transaction
    _transaction = db.transaction('begin')

    // TODO: Article 데이터 삭제 (파일, 댓글, 태그)
    // TODO: Nest 데이터 삭제 (카테고리)

    // delete app data
    db.deleteData({
      table: DB.TABLE.APP,
      where: _where,
      debug: true,
    })

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to delete App.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
