/**
 * ProviderGoogle
 * 구글 인증 서비스 인터페이스 클래스
 */

import Provider from './Provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from './assets'
import { parseQueryString } from '@/libs/strings'
import { PATHS } from '@/libs/assets'
import ServiceError from "@/classes/ServiceError.ts";

export default class ProviderGoogle extends Provider {

  public code = PROVIDER_CODE.GOOGLE
  public type = PROVIDER_TYPE.OAUTH
  public description = `oAuth by ${PROVIDER_CODE.GOOGLE}`
  private scope = 'openid email profile'
  private headerType = 'Bearer'
  private clientId = Bun.env.AUTH_GOOGLE_CLIENT_ID
  private clientSecret = Bun.env.AUTH_GOOGLE_CLIENT_SECRET
  private url_authorization = 'https://accounts.google.com/o/oauth2/v2/auth'
  private url_token = 'https://oauth2.googleapis.com/token'
  private url_userinfo = 'https://www.googleapis.com/oauth2/v2/userinfo'

  constructor() {
    super()
  }

  static checkUserId(userId: string, userData: ZZ): boolean
  {
    return userId === userData.id
  }

  public createAuthorizeUrl(state: string): string
  {
    const _query = parseQueryString({
      client_id: this.clientId,
      redirect_uri: `${PATHS.URL}/auth/callback/${this.code}/`,
      response_type: 'code',
      scope: this.scope,
      prompt: 'consent',
      access_type: 'offline',
      state,
    })
    return `${this.url_authorization}?${_query}`
  }

  public async getToken(code: string): Promise<ZZ>
  {
    const res = await fetch(this.url_token, {
      method: 'post',
      headers: { 'Accept': 'application/json' },
      body: new URLSearchParams({
        client_id: this.clientId,
        client_secret: this.clientSecret,
        code,
        grant_type: 'authorization_code',
        redirect_uri: `${PATHS.URL}/auth/callback/${this.code}/`,
      } as ZZ),
    })
    const _json: any = await res.json()
    if (!res?.ok)
    {
      throw new ServiceError('인증 서비스 토큰을 가져올 수 없습니다.', {
        status: 401,
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

  public async getUser(accessToken: string): Promise<ZZ>
  {
    const res = await fetch(this.url_userinfo, {
      method: 'get',
      headers: { 'Authorization': `${this.headerType} ${accessToken}` },
    })
    const _json = (await res.json()) as ZZ
    if (!_json?.id) throw new Error('응답 데이터가 없습니다.')
    return {
      id: _json.id,
      name: _json.name,
      email: _json.email,
      avatar: _json.picture,
    }
  }

  public async renewToken(): Promise<ZZ>
  {
    // TODO
  }

}
