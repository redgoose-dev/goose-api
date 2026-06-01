/**
 * ProviderGoogle
 * 구글 인증 서비스 인터페이스 클래스
 */

import Provider from './Provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from './assets'

export default class ProviderGoogle extends Provider {

  public code = PROVIDER_CODE.GOOGLE
  public type = PROVIDER_TYPE.OAUTH
  public description = `oAuth by ${PROVIDER_CODE.GOOGLE}`
  private scope = 'user'
  private headerType = 'Bearer'
  private clientId = Bun.env.AUTH_GOOGLE_CLIENT_ID
  private clientSecret = Bun.env.AUTH_GOOGLE_CLIENT_SECRET

  constructor() {
    super()
  }

}
