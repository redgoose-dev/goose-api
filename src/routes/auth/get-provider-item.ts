import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { CheckinToken } from '@/libs/verify'

type GetProviderItem = {
  srl: number
  token: CheckinToken
}

export default async function getProviderItem({ srl, token }: GetProviderItem)
{
  try
  {
    // set srl
    let _srl: number
    if (srl)
    {
      _srl = srl
    }
    else if (token?.provider_srl)
    {
      _srl = token.provider_srl
    }
    else
    {
      throw new ServiceError('Not found srl.', { status: 400 })
    }

    // set where
    const _where = `srl = ${_srl}`

    // get provider
    const provider = db.getData({
      table: DB.TABLE.PROVIDER,
      where: _where,
    })
    if (!provider.data)
    {
      throw new ServiceError('Not found provider data.', { status: 204 })
    }
    if (provider.data?.user_password) delete provider.data.user_password

    return provider.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed get Provider item.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
