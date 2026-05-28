/**
 * ProviderPassword
 * 비밀번호 인증 인터페이스 클래스
 */

import { hashSync, genSaltSync, compareSync } from 'bcryptjs'
import { sign, verify } from 'jsonwebtoken'
import type { SignOptions } from 'jsonwebtoken'
import Provider from './Provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from './assets'

type RenewTokenParams = {
  refreshToken?: string
  provider?: ZZ
}
type RenewTokenReturn = {
  access: string
  accessPublic: string
  refresh: string
  expires: number
}

const PASSWORD_HASH_ROUND = 9

export default abstract class ProviderPassword extends Provider {

  static code = PROVIDER_CODE.PASSWORD
  static type = PROVIDER_TYPE.PASSWORD
  static description = 'password login'
  static accessSecret = Bun.env.AUTH_PASSWORD_ACCESS_SECRET as string
  static accessExpires = Bun.env.AUTH_PASSWORD_ACCESS_EXPIRES as SignOptions['expiresIn']
  static refreshSecret = Bun.env.AUTH_PASSWORD_REFRESH_SECRET as string
  static refreshExpires = Bun.env.AUTH_PASSWORD_REFRESH_EXPIRES as SignOptions['expiresIn']

  static hashPassword(pw: string): string
  {
    const salt = genSaltSync(PASSWORD_HASH_ROUND)
    return hashSync(pw, salt)
  }

  static verifyPassword(password: string, hashedPassword: string): boolean
  {
    return compareSync(String(password), hashedPassword)
  }

  // 새로운 토큰 제작
  static newToken(type: 'access'|'refresh', payload: ZZ = {}): ZZ
  {
    let code: string
    switch (type)
    {
      case 'access':
      {
        code = sign(payload, this.accessSecret, { expiresIn: this.accessExpires })
        return {
          code,
          parsed: verify(code, this.accessSecret)
        }
      }
      case 'refresh':
        code = sign(payload, this.refreshSecret, { expiresIn: this.refreshExpires })
        return {
          code,
          parsed: verify(code, this.refreshSecret)
        }
    }
  }

  static async renewToken(op: RenewTokenParams): Promise<RenewTokenReturn>
  {
    if (!op.provider)
    {
      throw new Error('Not found provider data.')
    }
    const _access = this.newToken('access', {
      srl: op.provider.srl,
      user_id: op.provider.user_id,
    })
    const _refresh = this.newToken('refresh', {
      srl: op.provider.srl,
    })
    const _expires = this.convertExpToRemainTime(_access.parsed.exp)
    return {
      access: _access.code,
      accessPublic: this.getPublicToken(_access.code),
      refresh: _refresh.code,
      expires: _expires,
    }
  }

}
