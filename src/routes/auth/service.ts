import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { AuthModel } from './model'

export abstract class Auth {

  static postLogin(op: AuthModel['postLoginBody'])
  {
    // TODO: get provider instance
    // TODO: get provider data
    // TODO: verify password
    // TODO: create new token
    // TODO: add token data
    // TODO: result
    console.log('postLogin()', op)
    return {
      'access': 'a',
      'expires': 'b',
      'refresh': 'c',
    }
  }

}
