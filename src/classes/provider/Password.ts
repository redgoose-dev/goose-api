/**
 * ProviderPassword
 * 비밀번호 인증 인터페이스 클래스
 */

import { hashSync, genSaltSync, compareSync } from 'bcryptjs'
import Provider from './Provider'

const PASSWORD_HASH_ROUND = 9

class ProviderPassword extends Provider {

  constructor(_ctx: any)
  {
    super()
  }

  hashPassword(pw: string): string
  {
    const salt = genSaltSync(PASSWORD_HASH_ROUND)
    return hashSync(pw, salt)
  }

}

export default ProviderPassword
