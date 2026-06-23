import DB, { db } from '@/classes/DB'
import * as tagHelper from '@/routes/tag/__helper'
import * as fileHelper from '@/routes/file/__helper'
import * as commentHelper from '@/routes/comment/__helper'
import type { BaseModel } from '@/libs/models'

export const STATUS = {
  READY: 'ready',
  PUBLIC: 'public',
  PRIVATE: 'private',
  check(value: string): boolean
  {
    return [ STATUS.PUBLIC, STATUS.PRIVATE ].includes(value as any)
  },
} as const

export function count({ table, where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: table ?? DB.TABLE.ARTICLE,
    where: where ?? '',
    values: values ?? {},
  })
  return count.data || 0
}

export async function remove(srl: number)
{
  // comment data
  await commentHelper.removeWithModule({
    module: commentHelper.MODULE.ARTICLE,
    module_srl: srl,
  })
  // tag data
  tagHelper.remove({
    module: tagHelper.MODULE.ARTICLE,
    module_srl: srl,
  })
  // file data
  await fileHelper.remove({
    module: fileHelper.MODULE.ARTICLE,
    module_srl: srl,
  })
  // article data
  db.deleteData({
    table: DB.TABLE.ARTICLE,
    where: `srl = ${srl}`,
  })
}
