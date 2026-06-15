import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import MOD from '@/classes/MOD'
import type { ChecklistModel } from './__model'

type GetItemParams = {
  srl: number
  query: ChecklistModel['getItemQuery']
}

export default async function getItem({ srl, query }: GetItemParams)
{
  try
  {
    // set field
    const _field = query.field ? query.field.split(',') : ''

    // get data
    const item = db.getData({
      table: DB.TABLE.CHECKLIST,
      field: _field,
      where: `srl = ${srl}`,
    })
    if (!item.data) throw new ServiceError('No data', { status: 204 })

    // set mod
    const _mod: MOD = new MOD(query.mod)

    // MOD / count-file
    if (_mod.check('count-file'))
    {
      // TODO
    }
    // MOD / tag
    if (_mod.check('tag'))
    {
      // TODO
    }

    // return
    return {
      ...item.data,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Checklist item.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}


