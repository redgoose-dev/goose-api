/**
 * ProviderGoogle
 * 구글 인증 서비스 인터페이스 클래스
 */

import Provider from './Provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from './assets'

export default abstract class ProviderGoogle extends Provider {

  static code = PROVIDER_CODE.GOOGLE
  static type = PROVIDER_TYPE.OAUTH
  static description = `oAuth by ${PROVIDER_CODE.GOOGLE}`
  static scope = 'user'
  static headerType = 'Bearer'
  static clientId = Bun.env.AUTH_GOOGLE_CLIENT_ID
  static clientSecret = Bun.env.AUTH_GOOGLE_CLIENT_SECRET

  static createAuthorizeUrl()
  {}

  static async getToken()
  {}

  static async getUser()
  {}

  static checkUserId()
  {}

  static async renewToken()
  {}

}
