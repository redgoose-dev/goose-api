import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import ProviderPassword from '@/classes/provider/Password'
import type { AuthModel } from './__model'

type PutProviderItemParams = {
  body: AuthModel['putProviderBody']
}

export default async function putProviderItem({ body }: PutProviderItemParams)
{
  try
  {
    const __provider__ = new ProviderPassword()

    // check exist provider
    const count = db.getCount({
      table: DB.TABLE.PROVIDER,
      where: `code LIKE $code`,
      values: { '$code': __provider__.code },
    })
    if (count.data > 0)
    {
      throw new ServiceError('Exist provider data.', { status: 400 })
    }

    // set password
    const hashedPassword = ProviderPassword.hashPassword(body.password)

    // add provider data
    db.addData({
      table: DB.TABLE.PROVIDER,
      values: [
        { key: 'code', value: __provider__.code },
        { key: 'user_id', value: body.id },
        { key: 'user_name', value: body.name },
        { key: 'user_avatar', value: body.avatar },
        { key: 'user_email', value: body.email },
        { key: 'user_password', value: hashedPassword },
        { key: 'created_at', valueName: DB.DATE_TIME },
      ],
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed add provider.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
