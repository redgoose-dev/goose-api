/**
 * Provider
 */

import type { ProviderCode } from './assets'
import { PATHS } from '@/libs/assets'

export default abstract class Provider {

  /**
   * 만료시간을 남은시간으로 변환
   */
  static convertExpToRemainTime(exp: number): number
  {
    const now = Math.floor(Date.now() / 1000)
    return exp - now
  }

  /**
   * 시간이 만료되었는지 검사
   * @param {number} createdTime 만들어진 시간(초)
   * @param {number} expired 만료시간(초)
   * @return 만료되었으면 true
   */
  checkRemainTime(createdTime: number, expired: number): boolean
  {
    const _now = Date.now()
    const _target = (new Date(createdTime).getTime()) + (expired * 1000)
    return _target < _now
  }

  /**
   * 공개용 토큰 가져오기
   */
  static getPublicToken(code: string): string
  {
    return code.slice(-32)
  }

  static getAuthorizeLink(code: ProviderCode, redirectUri: string)
  {
    return `${PATHS.URL}/auth/redirect/${code}/?redirect_uri=${redirectUri}`
  }

}
