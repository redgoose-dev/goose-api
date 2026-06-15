import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { PROVIDER_CODE, PROVIDER_TYPE, getProvider } from '@/classes/provider'
import { arrayToObject } from '@/libs/objects'
import type { AuthModel } from './__model'

type PostReadyLogin = {
  body: AuthModel['postReadyLogin']
}

export default async function postReadyLogin({ body }: PostReadyLogin)
{
  try
  {
    // get providers
    const providers = db.getIndex({
      table: DB.TABLE.PROVIDER,
    })
    const _providers = arrayToObject(providers.data, 'code')

    // set providers data
    return Object.values(PROVIDER_CODE).map((code) => {
      if (!_providers[code]) return false
      const __provider__ = getProvider(code)
      if (__provider__.type !== PROVIDER_TYPE.OAUTH) return false
      return {
        name: code,
        auth_url: __provider__.getAuthorizeLink(code, body.redirect_uri),
      }
    }).filter(Boolean)
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed get ready login data.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
