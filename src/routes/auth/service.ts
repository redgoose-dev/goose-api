import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import ProviderPassword from '@/classes/provider/Password'
import { getProviderClass, type ProviderClass } from '@/classes/provider'
import { PROVIDER_CODE, PROVIDER_TYPE, type ProviderCode } from '@/classes/provider/assets'
import type { CheckinToken } from '@/libs/verify'
import type { AuthModel } from './model'

type PostReadyLogin = {
  redirectUri: string
}

export abstract class Auth {

  static postCheckin(token: CheckinToken)
  {
    try
    {
      const provider = db.getData({
        table: DB.TABLE.PROVIDER,
        where: `srl = ${token.provider_srl}`,
      })
      return {
        provider: {
          'srl': provider.data.srl,
          'code': provider.data.code,
          'user_id': provider.data.user_id,
          'user_name': provider.data.user_name,
          'user_avatar': provider.data.user_avatar,
          'user_email': provider.data.user_email,
        },
      }
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed checkin', {
        status: _e.status,
        text: _e.message,
        err: _e,
      })
    }
  }

  static async postRenew(token: CheckinToken, refreshToken: string)
  {
    let _transction = false
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
      const ProviderClass = getProviderClass(provider.data.code)
      // 새로운 엑세스 토큰 만들기
      // TODO: type 에서 password는 provider값이 필요하고 oauth는 refreshToken 값이 필요하다.
      const newToken = await ProviderClass.renewToken({
        provider: provider.data,
      })
      if (!newToken)
      {
        throw new ServiceError('Failed to renew access token.', { status: 400 })
      }
      _transction = db.transaction('begin')
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
          { key: 'description', value: ProviderClass.description },
          { key: 'created_at', valueName: DB.DATE_TIME },
        ],
        debug: true,
      })
      _transction = db.transaction('commit')
      return {
        access: newToken.accessPublic,
        refresh: newToken.refresh,
        expires: newToken.expires,
      }
    }
    catch (_e: any)
    {
      db.transaction('rollback', _transction)
      throw new ServiceError('Failed renew token.', {
        status: _e.status,
        text: _e.message,
        err: _e,
      })
    }
  }

  static postReadyLogin(op: PostReadyLogin)
  {
    try
    {
      // get providers
      const providers = db.getIndex({
        table: DB.TABLE.PROVIDER,
      })
      const _providers = providers.data.reduce((acc: any, cur: any) => {
        acc[cur.code] = cur
        return acc
      }, {})
      // set providers data
      return Object.values(PROVIDER_CODE).map((code) => {
        if (!_providers[code]) return false
        const ProviderClass = getProviderClass(code)
        if (ProviderClass.type !== PROVIDER_TYPE.OAUTH) return false
        return {
          name: code,
          auth_url: ProviderClass.getAuthorizeLink(code, op.redirectUri),
        }
      }).filter(Boolean)
    }
    catch (_e: any)
    {
      throw new ServiceError('Success get ready login data.', {
        status: _e.status,
        text: _e.message,
        err: _e,
      })
    }
  }

  static async postLogin(op: AuthModel['postLoginBody'])
  {
    try
    {
      // get provider data
      const provider = db.getData({
        table: DB.TABLE.PROVIDER,
        where: [
          `AND code LIKE $code`,
          `AND user_id LIKE $userId`,
        ],
        values: {
          '$code': ProviderPassword.code,
          '$userId': op.id,
        },
      })
      if (!provider.data)
      {
        throw new ServiceError('Provider not found.', { status: 401 })
      }
      // verify password
      const _verifyPassword = ProviderPassword.verifyPassword(op.password, provider.data.user_password)
      if (!_verifyPassword)
      {
        throw new ServiceError('Failed to verify password.', { status: 401 })
      }
      // create new token
      const newToken = await ProviderPassword.renewToken({
        provider: provider.data,
      })
      // add data
      db.addData({
        table: DB.TABLE.TOKEN,
        values: [
          { key: 'provider_srl', value: provider.data.srl },
          { key: 'access', value: newToken.access },
          { key: 'expires', value: newToken.expires },
          { key: 'refresh', value: newToken.refresh },
          { key: 'description', value: ProviderPassword.description },
          { key: 'created_at', valueName: DB.DATE_TIME },
        ],
      })
      return {
        access: newToken.accessPublic,
        expires: newToken.expires,
        refresh: newToken.refresh,
      }
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed login', {
        status: _e.status,
        text: _e.message,
        err: _e,
      })
    }
  }

}
