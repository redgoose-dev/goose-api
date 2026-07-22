import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import type { CheckinToken } from '@/libs/verify'

type PostLogoutParams = {
  token: CheckinToken
}

export default async function postLogout({ token }: PostLogoutParams)
{
  try
  {
    // 토큰의 만료시간을 0으로 변경
    db.editData({
      table: DB.TABLE.TOKEN,
      where: `srl = ${token.srl}`,
      set: [ 'expires = $expires' ],
      values: { '$expires': 0 },
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed logout', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
