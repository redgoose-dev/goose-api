import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import Provider from '@/classes/provider/Provider'
import type { AuthModel } from './__model'

type GetTokenIndexParams = {
  query: AuthModel['getTokenQuery']
}

export default async function getTokenIndex({ query }: GetTokenIndexParams)
{
  try
  {
    // expires = NULL 인 데이터만 조회 (공개용 토큰은 NULL)
    let _where: string[] = [ 'AND expires IS NULL' ]
    let _values: ZZ = {}
    if (query.token)
    {
      _where.push(`AND access LIKE $access`)
      _values['$access'] = `%${query.token}`
    }

    // get count
    const count = db.getCount({
      table: DB.TABLE.TOKEN,
      where: _where,
      values: _values,
    })
    if (!(count.data > 0))
    {
      throw new ServiceError('Token not found.', { status: 204 })
    }

    // get data
    const index = db.getIndex({
      table: DB.TABLE.TOKEN,
      where: _where,
      values: _values,
      order: query.order,
      sort: query.sort,
    })

    // set MOD
    const _mod: MOD = new MOD(query.mod)

    // transform index
    if (index.data?.length > 0)
    {
      index.data = index.data.map((item: ZZ) => {
        // 안쓰는 키 삭제
        delete item.expires
        delete item.refresh
        // 공개용 엑세스 토큰으로 변환
        item.access = Provider.getPublicToken(item.access)
        // MOD / provider
        if (_mod.check('provider'))
        {
          item.provider = db.getData({
            table: DB.TABLE.PROVIDER,
            where: `srl = ${item.provider_srl}`,
          }).data
          if (item.provider) delete item.provider.user_password
        }
        return item
      })
    }
    else
    {
      index.data = []
    }

    return {
      total: count.data,
      index: index.data,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed get Token index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
