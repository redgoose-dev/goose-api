import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import type { AppModel } from './__model'

type GetItemParams = {
  srl?: number
  code?: string
  query: AppModel['getItemQuery']
}

export default async function getItem({ srl, code, query }: GetItemParams)
{
  try
  {
    // set field
    const _field = query.field ? query.field.split(',') : ''

    // set where
    let _where = ''
    if (srl) _where = `srl = ${srl}`
    else if (code) _where = `code LIKE \'${code}\'`

    // get item
    const item = db.getData({
      table: DB.TABLE.APP,
      field: _field,
      where: _where,
    })
    if (!item.data)
    {
      throw new ServiceError('App data not found.', { status: 204 })
    }

    // set data
    let data: ZZ = {
      ...(item ? item.data : {}) as ZZ,
    }
    // set MOD
    const _mod: MOD = new MOD(query.mod)
    // MOD / count-nest
    if (_mod.check('count-nest'))
    {
      // TODO: Nest 데이터 쌓이면 만들자
      console.log('MOD: count-nest')
    }
    // MOD / count-article
    if (_mod.check('count-article'))
    {
      // TODO: Article 데이터 쌓이면 만들자
      console.log('MOD: count-article')
    }

    return data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get App.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
