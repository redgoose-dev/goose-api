/**
 * ProviderDiscord
 * 디스코드 인증 서비스 인터페이스 클래스
 */

import Provider from './Provider'

export default abstract class ProviderDiscord extends Provider {

  static code = 'discord'
  static type = 'OAuth'
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

  static async renewAccessToken()
  {}

}
