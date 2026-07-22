import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { CheckinToken } from '@/libs/verify'

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
    return {
      provider: {
        'srl': provider.data.srl,
        'code': provider.data.code,
        'user_id': provider.data.user_id,
        'user_name': provider.data.user_name,
        'user_avatar': provider.data.user_avatar,
        'user_email': provider.data.user_email,
      },
    }
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
