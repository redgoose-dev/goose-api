import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import ProviderPassword from '@/classes/provider/Password'
import type { AuthModel } from './model'

export abstract class Auth {

  static postLogin(op: AuthModel['postLoginBody'])
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
      const accessToken = ProviderPassword.createToken('access', {
        srl: provider.data.srl,
        user_id: provider.data.user_id,
      })
      const refreshToken = ProviderPassword.createToken('refresh', {
        srl: provider.data.srl,
      })
      if (!(accessToken && refreshToken))
      {
        throw new ServiceError('Failed create token.', { status: 401 })
      }
      const accessTokenMaxAge = ProviderPassword.convertExpToRemainTime(accessToken.parsed.exp)
      // add data
      db.addData({
        table: DB.TABLE.TOKEN,
        values: [
          { key: 'provider_srl', value: provider.data.srl },
          { key: 'access', value: accessToken.code },
          { key: 'expires', value: accessTokenMaxAge },
          { key: 'refresh', value: refreshToken.code },
          { key: 'description', value: 'password login' },
          { key: 'created_at', valueName: DB.DATE_TIME },
        ].filter(Boolean),
      })
      return {
        access: ProviderPassword.getPublicToken(accessToken.code),
        expires: accessTokenMaxAge,
        refresh: refreshToken.code,
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
