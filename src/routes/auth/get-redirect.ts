import ServiceError from '@/classes/ServiceError'
import { getProvider } from '@/classes/provider'
import { encodeUri } from '@/libs/strings'
import type { ProviderCode } from '@/classes/provider'
import type { AuthModel } from './__model'

type GetRedirectParams = {
  code: string
  query: AuthModel['getRedirectQuery']
}

/**
 * OAuth 서비스로 리다이렉트한다.
 *
 * # URL Example
 * GET /auth/redirect/discord/?redirect_uri={CLIENT_REDIRECT_URI}
 */
export default async function getRedirect({ code, query }: GetRedirectParams)
{
  // set state
  const state = encodeUri({
    redirect_uri: query.redirect_uri,
    access_token: query.access_token,
  })

  // get provider class
  const __provider__ = getProvider(code as ProviderCode)
  const url = __provider__.createAuthorizeUrl(state)
  if (!url) throw new ServiceError('Failed get redirect url.')

  return url
}
