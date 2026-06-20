import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as helper from './__helper'
import type { CategoryModel } from './__model'

type PutItemParams = {
  body: CategoryModel['putItemBody'],
}

export default async function putItem({ body }: PutItemParams)
{
  try
  {
    // checking module
    helper.checkingModule(body.module, body.module_srl)

    // checking exist name
    helper.checkingExistName({
      module: body.module,
      moduleSrl: body.module_srl,
      name: body.name,
    })

    // get count for max turn
    let _where = [ `AND module LIKE \'${body.module}\'` ]
    switch (body.module)
    {
      case helper.MODULE.NEST:
        if (body.module_srl) _where.push(`AND module_srl = ${body.module_srl}`)
        else _where.push(`AND module_srl IS NULL`)
        break
      case helper.MODULE.JSON:
        _where.push(`AND module_srl IS NULL`)
        break
    }
    const count = db.getCount({
      table: DB.TABLE.CATEGORY,
      where: _where,
    })

    // add data
    const added = db.addData({
      table: DB.TABLE.CATEGORY,
      values: [
        { key: 'name', value: body.name },
        { key: 'turn', value: count.data + 1 },
        { key: 'module', value: body.module },
        (body.module === helper.MODULE.NEST && body.module_srl) && { key: 'module_srl', value: body.module_srl },
        { key: 'created_at', valueName: DB.DATE_TIME },
      ],
    })

    return added.data
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to add Category.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
