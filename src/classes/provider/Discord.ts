/**
 * ProviderDiscord
 * 디스코드 인증 서비스 인터페이스 클래스
 */

import Provider from './Provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from './assets'

export default abstract class ProviderDiscord extends Provider {

  static code = PROVIDER_CODE.DISCORD
  static type = PROVIDER_TYPE.OAUTH
  static description = `oAuth by ${PROVIDER_CODE.DISCORD}`
  static scope = 'user'
  static headerType = 'Bearer'
  static clientId = Bun.env.AUTH_DISCORD_CLIENT_ID
  static clientSecret = Bun.env.AUTH_DISCORD_CLIENT_SECRET

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
