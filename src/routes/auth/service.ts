import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import ProviderPassword from '@/classes/provider/Password'
import { getProviderClass } from '@/classes/provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from '@/classes/provider/assets'
import { checkExistValueInObject } from '@/libs/objects'
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
        cause: _e,
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
        cause: _e,
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
        cause: _e,
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
        cause: _e,
      })
    }
  }

  static async postLogout(srl: number)
  {
    try
    {
      // 토큰의 만료시간을 0으로 변경
      db.editData({
        table: DB.TABLE.TOKEN,
        where: `srl = ${srl}`,
        set: [ 'expires = $expires' ],
        values: { '$expires': 0 },
      })
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed logout', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static putProvider(body: AuthModel['putProviderBody'])
  {
    try
    {
      // check exist provider
      const count = db.getCount({
        table: DB.TABLE.PROVIDER,
        where: `code LIKE $code`,
        values: { '$code': ProviderPassword.code },
      })
      if (count.data > 0)
      {
        throw new ServiceError('Exist provider data.', { status: 400 })
      }
      // set password
      const hashedPassword = ProviderPassword.hashPassword(body.password)
      // add provider data
      db.addData({
        table: DB.TABLE.PROVIDER,
        values: [
          { key: 'code', value: ProviderPassword.code },
          { key: 'user_id', value: body.id },
          { key: 'user_name', value: body.name },
          { key: 'user_avatar', value: body.avatar },
          { key: 'user_email', value: body.email },
          { key: 'user_password', value: hashedPassword },
          { key: 'created_at', valueName: DB.DATE_TIME },
        ],
      })
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed add provider.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static patchProvider(srl: number, body: AuthModel['patchProviderBody'])
  {
    try
    {
      // check exist data
      const countProvider = db.getCount({
        table: DB.TABLE.PROVIDER,
        where: `srl = ${srl}`,
      })
      if (!(countProvider.data > 0))
      {
        throw new ServiceError('Provider not found.', { status: 204 })
      }
      // if `body.id` then check exist id
      if (body.id)
      {
        const countProvider = db.getCount({
          table: DB.TABLE.PROVIDER,
          where: `user_id LIKE $userId`,
          values: { '$userId': body.id },
        })
        if (countProvider.data > 0)
        {
          throw new ServiceError('ID already exists.', { status: 400 })
        }
      }
      // set ready update
      let _ready: ZZ = {
        id: undefined,
        name: undefined,
        avatar: undefined,
        email: undefined,
        password: undefined,
      }
      if (body.id !== undefined) _ready['id'] = body.id
      if (body.name !== undefined) _ready['name'] = body.name
      if (body.avatar !== undefined) _ready['avatar'] = body.avatar
      if (body.email !== undefined) _ready['email'] = body.email
      if (body.password !== undefined) _ready['password'] = ProviderPassword.hashPassword(body.password)
      // update data
      if (!checkExistValueInObject(_ready, Object.keys(_ready)))
      {
        throw new ServiceError('Nothing to update.', { status: 400 })
      }
      db.editData({
        table: DB.TABLE.PROVIDER,
        where: `srl = ${srl}`,
        set: [
          _ready.id !== undefined && 'user_id = $id',
          _ready.name !== undefined && 'user_name = $name',
          _ready.avatar !== undefined && 'user_avatar = $avatar',
          _ready.email !== undefined && 'user_email = $email',
          _ready.password !== undefined && 'user_password = $password',
        ],
        values: {
          '$id': _ready.id,
          '$name': _ready.name,
          '$avatar': _ready.avatar,
          '$email': _ready.email,
          '$password': _ready.password,
        },
      })
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed update provider.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static deleteProvider(srl: number)
  {
    let _transction = false
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
      _transction = db.transaction('begin')
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
      _transction = db.transaction('commit')
    }
    catch (_e: any)
    {
      db.transaction('rollback', _transction)
      throw new ServiceError('Failed delete provider.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

}
