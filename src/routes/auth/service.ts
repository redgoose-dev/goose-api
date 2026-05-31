import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Provider from '@/classes/provider/Provider'
import ProviderPassword from '@/classes/provider/Password'
import MOD from '@/classes/MOD'
import { getProviderClass } from '@/classes/provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from '@/classes/provider/assets'
import { checkExistValueInObject, arrayToObject } from '@/libs/objects'
import type { CheckinToken } from '@/libs/verify'
import type { AuthModel } from './model'

type PostReadyLogin = {
  redirectUri: string
}

export abstract class Auth {

  static async postCheckin(token: CheckinToken)
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

  static async postReadyLogin(op: PostReadyLogin)
  {
    try
    {
      // get providers
      const providers = db.getIndex({
        table: DB.TABLE.PROVIDER,
      })
      const _providers = arrayToObject(providers.data, 'code')
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
      throw new ServiceError('Failed get ready login data.', {
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

  static async getProviderIndex(query: AuthModel['getProviderIndexQuery'])
  {
    try
    {
      const providers = db.getIndex({
        table: DB.TABLE.PROVIDER,
      })
      const _providers = arrayToObject(providers.data, 'code')
      // set providers data
      const index = Object.values(PROVIDER_CODE).map((code) => {
        if (_providers[code])
        {
          const { user_password, ...rest } = _providers[code]
          return {
            account: rest,
            auth_url: null,
          }
        }
        else
        {
          const ProviderClass = getProviderClass(code)
          return {
            account: null,
            auth_url: ProviderClass.getAuthorizeLink(code, query.redirect_uri)
          }
        }
      })
      return {
        total: index.length,
        index,
      }
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed to get Provider index.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async getProvider(srl: number, token: CheckinToken)
  {
    // set srl
    let _srl: number
    if (srl)
    {
      _srl = srl
    }
    else if (token?.provider_srl)
    {
      _srl = token.provider_srl
    }
    else
    {
      throw new ServiceError('Not found srl.', { status: 400 })
    }
    // set where
    const _where = `srl = ${_srl}`
    // get provider
    const provider = db.getData({
      table: DB.TABLE.PROVIDER,
      where: _where,
    })
    if (!provider.data)
    {
      throw new ServiceError('Not found provider data.', { status: 204 })
    }
    if (provider.data?.user_password) delete provider.data.user_password
    return provider.data
  }

  static async putProvider(body: AuthModel['putProviderBody'])
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

  static async patchProvider(srl: number, body: AuthModel['patchProviderBody'])
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

  static async deleteProvider(srl: number)
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

  static async getTokens(query: AuthModel['getTokenQuery'])
  {
    try
    {
      // expires = NULL 인 데이터만 조회 (공개용 토큰은 NULL)
      let _where: string[] = [ 'AND expires IS NULL' ]
      let _values: ZZ = {}
      if (query.token)
      {
        _where.push(`AND access LIKE $access`)
        _values['$access'] = `%${query.token}`
      }
      // get count
      const count = db.getCount({
        table: DB.TABLE.TOKEN,
        where: _where,
        values: _values,
      })
      if (!(count.data > 0))
      {
        throw new ServiceError('Token not found.', { status: 204 })
      }
      // get data
      const index = db.getIndex({
        table: DB.TABLE.TOKEN,
        where: _where,
        values: _values,
        order: query.order,
        sort: query.sort,
      })
      // set mod
      const _mod: MOD = new MOD(query.mod)
      // transform index
      if (index.data?.length > 0)
      {
        index.data = index.data.map((item: ZZ) => {
          // 안쓰는 키 삭제
          delete item.expires
          delete item.refresh
          // 공개용 엑세스 토큰으로 변환
          item.access = Provider.getPublicToken(item.access)
          // MOD / provider
          if (_mod.check('provider'))
          {
            item.provider = db.getData({
              table: DB.TABLE.PROVIDER,
              where: `srl = ${item.provider_srl}`,
            }).data
            if (item.provider) delete item.provider.user_password
          }
          return item
        })
      }
      else
      {
        index.data = []
      }
      return {
        total: count.data,
        index: index.data,
      }
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed get token index.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async putToken(body: AuthModel['putTokenBody'], token: CheckinToken)
  {
    try
    {
      // get provider
      const provider = db.getData({
        table: DB.TABLE.PROVIDER,
        where: `srl = ${token.provider_srl}`,
      })
      if (!provider.data)
      {
        throw new ServiceError('provider not found.', { status: 400 })
      }
      // create new token
      const newToken = await ProviderPassword.renewToken({
        provider: provider.data,
      })
      // add data
      const newSrl = db.addData({
        table: DB.TABLE.TOKEN,
        values: [
          { key: 'provider_srl', value: provider.data.srl },
          { key: 'access', value: newToken.access },
          { key: 'expires', valueNames: 'NULL' },
          { key: 'refresh', value: newToken.refresh },
          { key: 'description', value: body.description },
          { key: 'created_at', valueName: DB.DATE_TIME },
        ],
      })
      return {
        srl: newSrl.data,
        provider_srl: provider.data.srl,
        access: newToken.accessPublic,
        description: body.description,
      }
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed create public token.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async patchToken(srl: number, body: AuthModel['patchTokenBody'])
  {
    try
    {
      // get token count
      const count = db.getCount({
        table: DB.TABLE.TOKEN,
        where: `srl = ${srl} AND expires IS NULL`,
      })
      if (!(count.data > 0))
      {
        throw new ServiceError('Token not found.', { status: 204 })
      }
      // update data
      db.editData({
        table: DB.TABLE.TOKEN,
        where: `srl = ${srl}`,
        set: [ 'description = $description' ],
        values: { '$description': body.description },
      })
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed update public token.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async revokeToken(srl: number)
  {
    try
    {
      // get item
      const item = db.getData({
        table: DB.TABLE.TOKEN,
        where: `srl = ${srl} AND expires IS NULL`,
      })
      if (!item.data)
      {
        throw new ServiceError('Token not found.', { status: 204 })
      }
      // expires to 0
      db.editData({
        table: DB.TABLE.TOKEN,
        where: `srl = ${srl}`,
        set: [ `expires = $expires` ],
        values: { '$expires': 0 },
      })
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed update public token.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

}
