import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import { parseJSON } from '@/libs/objects'
import type { JsonModel } from './__model'

type GetItemParams = {
  srl: number
  query: JsonModel['getItemQuery']
}

export default async function getItem({ srl, query }: GetItemParams)
{
  try
  {
    // set assets
    let _table = `${DB.TABLE.JSON} AS j`
    const _field = query.field ? query.field.split(',') : ''

    // get data
    let item = db.getData({
      table: _table,
      field: _field,
      where: `srl = ${srl}`,
    })
    if (!item.data) throw new ServiceError('No data', { status: 204 })

    // set json data
    if (item.data.json)
    {
      item.data.json = parseJSON(item.data.json)
    }

    // set mod
    const _mod: MOD = new MOD(query.mod)

    // MOD / count-file
    if (_mod.check('count-file'))
    {
      // TODO: file 테이블 데이터가 쌓이면 만들자
      // const _count = FileTool.count({
      //   where: [
      //     `AND module = \'${FILE_MODULE.JSON}\'`,
      //     `AND module_srl = ${srl}`,
      //   ],
      // })
    }

    return item.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get JSON.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
