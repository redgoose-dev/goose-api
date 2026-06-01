/**
 * ProviderDiscord
 * 디스코드 인증 서비스 인터페이스 클래스
 */

import ServiceError from '@/classes/ServiceError'
import Provider from './Provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from './assets'
import { encodeUri, parseQueryString } from '@/libs/strings'
import { PATHS } from '@/libs/assets'

export default class ProviderDiscord extends Provider {

  public code = PROVIDER_CODE.DISCORD
  public type = PROVIDER_TYPE.OAUTH
  public description = `oAuth by ${PROVIDER_CODE.DISCORD}`
  private scope = 'email identify'
  private headerType = 'Bearer'
  private clientId = Bun.env.AUTH_DISCORD_CLIENT_ID
  private clientSecret = Bun.env.AUTH_DISCORD_CLIENT_SECRET
  private url_authorization = 'https://discord.com/oauth2/authorize'
  private url_token = 'https://discord.com/api/oauth2/token'
  private url_userinfo = 'https://discord.com/api/users/@me'

  constructor() {
    super()
  }

  public createAuthorizeUrl(state: string)
  {
    const _query = parseQueryString({
      client_id: this.clientId,
      redirect_uri: `${PATHS.URL}/auth/callback/${this.code}/`,
      response_type: 'code',
      scope: this.scope,
      state,
    })
    return `${this.url_authorization}/?${_query}`
  }

  public async getToken(code: string)
  {
    const res = await fetch(this.url_token, {
      method: 'post',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        client_id: this.clientId,
        client_secret: this.clientSecret,
        grant_type: 'authorization_code',
        code,
        redirect_uri: `${PATHS.URL}/auth/callback/${this.code}/`,
      } as ZZ)
    })
    const _json: any = await res.json()
    if (!res?.ok)
    {
      throw new ServiceError('인증 서비스 토큰을 가져올 수 없습니다.', {
        text: _json.error_description,
      })
    }
    if (!_json?.access_token)
    {
      throw new Error('응답 데이터에서 엑세스 토큰이 없습니다.')
    }
    return {
      access: _json.access_token,
      expires: _json.expires_in,
      refresh: _json.refresh_token,
    }
  }

  static async getUser(token: string): Promise<ZZ | null>
  {
    return null
  }

  static checkUserId(userId: string, userData: ZZ): boolean
  {
    return false
  }

  public async renewToken(): Promise<ZZ>
  {
    return {}
  }

}
