import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { PROVIDER_CODE, getProvider } from '@/classes/provider'
import { arrayToObject } from '@/libs/objects'
import type { AuthModel } from './__model'

type GetProviderIndexParams = {
  query: AuthModel['getProviderIndexQuery']
}

export default async function getProviderIndex({ query }: GetProviderIndexParams)
{
  try
  {
    const providers = db.getIndex({
      table: DB.TABLE.PROVIDER,
    })
    const _providers = arrayToObject(providers.data, 'code')

    // set providers data
    const index = Object.values(PROVIDER_CODE).map((code) => {
      if (_providers[code])
      {
        const { user_password, ...rest } = _providers[code]
        return {
          account: rest,
          auth_url: null,
        }
      }
      else
      {
        const __provider__ = getProvider(code)
        return {
          account: null,
          auth_url: __provider__.getAuthorizeLink(code, query.redirect_uri)
        }
      }
    })

    return {
      total: index.length,
      index,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Provider index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
