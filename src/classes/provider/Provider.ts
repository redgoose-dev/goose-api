/**
 * Provider
 */

import type { ProviderCode } from './'
import { PATHS } from '@/libs/assets'

abstract class Provider {

  constructor() {}

  /**
   * 공개용 토큰 가져오기
   */
  static getPublicToken(code: string, n: number = 32): string
  {
    return code.slice(0 - n)
  }

  /**
   * 만료시간을 남은시간으로 변환
   */
  static convertExpToRemainTime(exp: number): number
  {
    const now = Math.floor(Date.now() / 1000)
    return exp - now
  }

  public getAuthorizeLink(code: ProviderCode, redirectUri: string)
  {
    return `${PATHS.URL}/auth/redirect/${code}/?redirect_uri=${redirectUri}`
  }

}

export default Provider
