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

  // TODO: 안쓸수도 있음
  // /**
  //  * 시간이 만료되었는지 검사
  //  * @param {number} createdTime 만들어진 시간(초)
  //  * @param {number} expired 만료시간(초)
  //  * @return 만료되었으면 true
  //  */
  // public checkRemainTime(createdTime: number, expired: number): boolean
  // {
  //   const _now = Date.now()
  //   const _target = (new Date(createdTime).getTime()) + (expired * 1000)
  //   return _target < _now
  // }

  public getAuthorizeLink(code: ProviderCode, redirectUri: string)
  {
    return `${PATHS.URL}/auth/redirect/${code}/?redirect_uri=${redirectUri}`
  }

}

export default Provider
