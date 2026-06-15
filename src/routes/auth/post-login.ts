import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import ProviderPassword from '@/classes/provider/Password'
import type { AuthModel } from './__model'

type PostLoginParams = {
  body: AuthModel['postLoginBody']
}

export default async function postLogin({ body }: PostLoginParams)
{
  try
  {
    // set provider instance
    const __provider__ = new ProviderPassword()

    // get provider data
    const provider = db.getData({
      table: DB.TABLE.PROVIDER,
      where: [
        `AND code LIKE $code`,
        `AND user_id LIKE $userId`,
      ],
      values: {
        '$code': __provider__.code,
        '$userId': body.id,
      },
    })
    if (!provider.data)
    {
      throw new ServiceError('Provider not found.', { status: 401 })
    }

    // verify password
    const _verifyPassword = __provider__.verifyPassword(body.password, provider.data.user_password)
    if (!_verifyPassword)
    {
      throw new ServiceError('Failed to verify password.', { status: 401 })
    }

    // create new token
    const newToken = await __provider__.renewToken({
      provider: provider.data,
    })

    // add data
    db.addData({
      table: DB.TABLE.TOKEN,
      values: [
        { key: 'provider_srl', value: provider.data.srl },
        { key: 'access', value: newToken.access },
        { key: 'expires', value: newToken.expires },
        { key: 'refresh', value: newToken.refresh },
        { key: 'description', value: __provider__.description },
        { key: 'created_at', valueName: DB.DATE_TIME },
      ],
    })

    return {
      access: newToken.accessPublic,
      expires: newToken.expires,
      refresh: newToken.refresh,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed login', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
