import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import ProviderPassword from '@/classes/provider/Password'
import { PROVIDER_CODE } from '@/classes/provider'
import * as messages from '@/libs/messages'
import { parseProviderData } from './__helper'
import type { AuthModel } from './__model'

type PatchProviderItemParams = {
  srl: number
  body: AuthModel['patchProviderBody']
}

export default async function patchProviderItem({ srl, body }: PatchProviderItemParams)
{
  try
  {
    const provider = db.getData({
      table: DB.TABLE.PROVIDER,
      where: 'srl = $srl',
      values: { '$srl': srl },
    }).data
    if (!provider)
    {
      throw new ServiceError('Provider not found.', { status: 204 })
    }

    if (body.password !== undefined && provider.code !== PROVIDER_CODE.PASSWORD)
    {
      throw new ServiceError('Password is only supported by the password provider.', { status: 400 })
    }

    if (body.id !== undefined)
    {
      const count = db.getCount({
        table: DB.TABLE.PROVIDER,
        where: 'code = $code AND uid = $uid AND srl != $srl',
        values: { '$code': provider.code, '$uid': body.id, '$srl': srl },
      })
      if (count.data > 0)
      {
        throw new ServiceError('ID already exists.', { status: 400 })
      }
    }

    if (Object.values(body).every(value => value === undefined))
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }
    const data = parseProviderData(provider.data)
    if (body.name !== undefined) data.name = body.name
    if (body.avatar !== undefined) data.avatar = body.avatar
    if (body.email !== undefined) data.email = body.email
    if (body.password !== undefined) data.password = ProviderPassword.hashPassword(body.password)

    db.editData({
      table: DB.TABLE.PROVIDER,
      where: 'srl = $srl',
      set: [ 'uid = $uid', 'data = $data' ],
      values: {
        '$srl': srl,
        '$uid': body.id ?? provider.uid,
        '$data': JSON.stringify(data),
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
