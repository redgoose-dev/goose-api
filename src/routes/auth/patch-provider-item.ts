import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import ProviderPassword from '@/classes/provider/Password'
import * as messages from '@/libs/messages'
import { filteringObject } from '@/libs/objects'
import type { AuthModel } from './__model'

type PatchProviderItemParams = {
  srl: number
  body: AuthModel['patchProviderBody']
}

export default async function patchProviderItem({ srl, body }: PatchProviderItemParams)
{
  try
  {
    // check exist data
    const countProvider = db.getCount({
      table: DB.TABLE.PROVIDER,
      where: `srl = ${srl}`,
    })
    if (!(countProvider.data > 0))
    {
      throw new ServiceError('Provider not found.', { status: 204 })
    }

    // if `body.id` then check exist id
    if (body.id)
    {
      const countProvider = db.getCount({
        table: DB.TABLE.PROVIDER,
        where: `user_id LIKE $userId`,
        values: { '$userId': body.id },
      })
      if (countProvider.data > 0)
      {
        throw new ServiceError('ID already exists.', { status: 400 })
      }
    }

    // set ready update
    let _ready: ZZ = {
      id: undefined,
      name: undefined,
      avatar: undefined,
      email: undefined,
      password: undefined,
    }
    if (body.id !== undefined) _ready['id'] = body.id
    if (body.name !== undefined) _ready['name'] = body.name
    if (body.avatar !== undefined) _ready['avatar'] = body.avatar
    if (body.email !== undefined) _ready['email'] = body.email
    if (body.password !== undefined) _ready['password'] = ProviderPassword.hashPassword(body.password)

    // update data
    _ready = filteringObject(_ready)
    if (Object.keys(_ready).length <= 0)
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }
    db.editData({
      table: DB.TABLE.PROVIDER,
      where: `srl = ${srl}`,
      set: [
        _ready.id !== undefined && 'user_id = $id',
        _ready.name !== undefined && 'user_name = $name',
        _ready.avatar !== undefined && 'user_avatar = $avatar',
        _ready.email !== undefined && 'user_email = $email',
        _ready.password !== undefined && 'user_password = $password',
      ],
      values: {
        '$id': _ready.id,
        '$name': _ready.name,
        '$avatar': _ready.avatar,
        '$email': _ready.email,
        '$password': _ready.password,
      },
    })
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed update provider.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
