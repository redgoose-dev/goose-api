import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import ProviderPassword from '@/classes/provider/Password'
import type { CheckinToken } from '@/libs/verify'
import type { AuthModel } from './__model'

type PutTokenItemParams = {
  body: AuthModel['putTokenBody']
  token: CheckinToken
}

export default async function putTokenItem({ body, token }: PutTokenItemParams)
  {
    try
    {
      // get provider
      const provider = db.getData({
        table: DB.TABLE.PROVIDER,
        where: `srl = ${token.provider_srl}`,
      })
      if (!provider.data)
      {
        throw new ServiceError('provider not found.', { status: 400 })
      }

      // set provider instance
      const __provider__ = new ProviderPassword()

      // 공개용 토큰 만들기 (곧장 만들기 위하여 ProviderPassword 클래스 사용)
      const newToken = await __provider__.renewToken({
        provider: provider.data,
      })

      // add data
      const newSrl = db.addData({
        table: DB.TABLE.TOKEN,
        values: [
          { key: 'provider_srl', value: provider.data.srl },
          { key: 'access', value: newToken.access },
          { key: 'expires', valueNames: 'NULL' },
          { key: 'refresh', value: newToken.refresh },
          { key: 'description', value: body.description },
          { key: 'created_at', valueName: DB.DATE_TIME },
        ],
      })

      return {
        srl: newSrl.data,
        provider_srl: provider.data.srl,
        access: newToken.accessPublic,
        description: body.description,
      }
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed create public Token.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }
