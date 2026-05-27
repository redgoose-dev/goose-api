/**
 * ProviderGithub
 * 깃허브 인증 서비스 인터페이스 클래스
 */

import Provider from './Provider'

export default abstract class ProviderGithub extends Provider {

  static code = 'github'
  static type = 'OAuth'
  static scope = 'user'
  static headerType = 'Bearer'
  static clientId = Bun.env.AUTH_GITHUB_CLIENT_ID
  static clientSecret = Bun.env.AUTH_GITHUB_CLIENT_SECRET

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
