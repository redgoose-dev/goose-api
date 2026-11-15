import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { CheckinToken } from '@/libs/verify'
import { getPublicProvider } from './__helper'

type PostCheckinParams = {
  token: CheckinToken
}

export default async function postCheckin({ token }: PostCheckinParams)
{
  try
  {
    const provider = db.getData({
      table: DB.TABLE.PROVIDER,
      where: `srl = ${token.provider_srl}`,
    })
    const { created_at, ...account } = getPublicProvider(provider.data)
    return { provider: account }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed checkin', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
