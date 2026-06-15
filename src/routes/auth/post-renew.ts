import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { getProvider } from '@/classes/provider'
import type { CheckinToken } from '@/libs/verify'

type PostRenewParams = {
  token: CheckinToken
  refreshToken: string
}

export default async function postRenew({ token, refreshToken }: PostRenewParams)
{
  let _transaction = false
  try
  {
    // check refresh token
    if (token.refresh !== refreshToken)
    {
      throw new ServiceError('Invalid refresh token.', { status: 400 })
    }

    // 프로바이더 데이터 가져오기
    const provider = db.getData({
      table: DB.TABLE.PROVIDER,
      where: `srl = ${token.provider_srl}`,
    })

    // 프로바이더 클래스 가져오기
    const __provider__ = getProvider(provider.data.code)

    // 새로운 엑세스 토큰 만들기
    const newToken = await __provider__.renewToken({
      provider: provider.data,
      refresh: refreshToken,
    })
    if (!newToken?.access)
    {
      throw new ServiceError('Failed to renew access token.', { status: 400 })
    }

    // begin transaction
    _transaction = db.transaction('begin')

    // 이전 토큰의 만료시간을 0으로 변경
    db.editData({
      table: DB.TABLE.TOKEN,
      where: `srl = ${token.srl}`,
      set: [ 'expires = $expires' ],
      values: { '$expires': 0 },
    })

    // 토큰 데이터 추가
    db.addData({
      table: DB.TABLE.TOKEN,
      values: [
        { key: 'provider_srl', value: provider.data.srl },
        { key: 'access', value: newToken.access },
        { key: 'expires', value: newToken.expires },
        { key: 'refresh', value: newToken.refresh },
        { key: 'description', value: __provider__.description },
        { key: 'created_at', valueName: DB.DATE_TIME },
      ],
    })

    // commit transaction
    _transaction = db.transaction('commit')

    return {
      access: newToken.accessPublic,
      refresh: newToken.refresh,
      expires: newToken.expires,
    }
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed renew token.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
