/**
 * ProviderGithub
 * 깃허브 인증 서비스 인터페이스 클래스
 */

import ServiceError from '@/classes/ServiceError'
import Provider from './Provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from './'
import { parseQueryString } from '@/libs/strings'
import { PATHS } from '@/libs/assets'

export default class ProviderGithub extends Provider {

  public code = PROVIDER_CODE.GITHUB
  public type = PROVIDER_TYPE.OAUTH
  public description = `oAuth by ${PROVIDER_CODE.GITHUB}`
  private scope = 'user'
  private headerType = 'Bearer'
  private clientId = Bun.env.AUTH_GITHUB_CLIENT_ID
  private clientSecret = Bun.env.AUTH_GITHUB_CLIENT_SECRET
  private url_authorization = 'https://github.com/login/oauth/authorize'
  private url_token = 'https://github.com/login/oauth/access_token'
  private url_userinfo = 'https://api.github.com/user'

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
      scope: this.scope,
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
      avatar: _json.avatar_url,
    }
  }

  public async renewToken(op: ZZ): Promise<ZZ|undefined>
  {
    if (!op.refresh) return
    const res = await fetch(this.url_token, {
      method: 'post',
      headers: { 'Accept': 'application/json' },
      body: new URLSearchParams({
        client_id: this.clientId,
        client_secret: this.clientSecret,
        grant_type: 'refresh_token',
        refresh_token: op.refresh,
      } as ZZ)
    })
    const _json: any = await res.json()
    if (!res?.ok)
    {
      throw new ServiceError('엑세스 토큰 재발급 실패', {
        status: 401,
        text: _json.error_description,
      })
    }
    return {
      access: _json.access_token,
      accessPublic: Provider.getPublicToken(_json.access_token),
      refresh: _json.refresh_token,
      expires: _json.expires_in,
    }
  }

}
