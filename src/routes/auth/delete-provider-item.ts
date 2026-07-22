import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'

export default async function deleteProviderItem(srl: number)
{
  let _transaction = false
  try
  {
    // check exist data
    const count = db.getCount({
      table: DB.TABLE.PROVIDER,
      where: `srl = ${srl}`,
    })
    if (!(count.data > 0))
    {
      throw new ServiceError('Provider not found.', { status: 204 })
    }

    // begin transaction
    _transaction = db.transaction('begin')

    // 프로바이더 삭제
    db.deleteData({
      table: DB.TABLE.PROVIDER,
      where: `srl = ${srl}`,
    })

    // 토큰 테이블에서 만료 시간을 0으로 변경
    db.editData({
      table: DB.TABLE.TOKEN,
      where: `provider_srl = ${srl}`,
      set: [ 'expires = 0' ],
    })

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed delete provider.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
