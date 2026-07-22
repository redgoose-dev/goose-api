/**
 * ProviderPassword
 * 비밀번호 인증 인터페이스 클래스
 */

import { hashSync, genSaltSync, compareSync } from 'bcryptjs'
import { sign, verify } from 'jsonwebtoken'
import type { SignOptions } from 'jsonwebtoken'
import Provider from './Provider'
import { PROVIDER_CODE, PROVIDER_TYPE } from './'

const PASSWORD_HASH_ROUND = 9

class ProviderPassword extends Provider {

  public code = PROVIDER_CODE.PASSWORD
  public type = PROVIDER_TYPE.PASSWORD
  public description = 'password login'
  private accessSecret = Bun.env.AUTH_PASSWORD_ACCESS_SECRET as string
  private accessExpires = Bun.env.AUTH_PASSWORD_ACCESS_EXPIRES as SignOptions['expiresIn']
  private refreshSecret = Bun.env.AUTH_PASSWORD_REFRESH_SECRET as string
  private refreshExpires = Bun.env.AUTH_PASSWORD_REFRESH_EXPIRES as SignOptions['expiresIn']

  static hashPassword(pw: string): string
  {
    const salt = genSaltSync(PASSWORD_HASH_ROUND)
    return hashSync(pw, salt)
  }

  public useProvider(): boolean
  {
    return Boolean(this.accessSecret && this.refreshSecret)
  }

  public verifyPassword(password: string, hashedPassword: string): boolean
  {
    return compareSync(String(password), hashedPassword)
  }

  // 새로운 토큰 제작
  public newToken(type: 'access'|'refresh', payload: ZZ = {}): ZZ
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

  public async renewToken(op: ZZ): Promise<ZZ>
  {
    if (!op.provider) throw new Error('Not found provider data.')
    const _access = this.newToken('access', {
      srl: op.provider.srl,
      user_id: op.provider.user_id,
    })
    const _refresh = this.newToken('refresh', {
      srl: op.provider.srl,
    })
    const _expires = Provider.convertExpToRemainTime(_access.parsed.exp)
    return {
      access: _access.code,
      accessPublic: Provider.getPublicToken(_access.code),
      refresh: _refresh.code,
      expires: _expires,
    }
  }

}

export default ProviderPassword
