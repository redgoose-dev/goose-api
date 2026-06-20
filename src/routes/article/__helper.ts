import DB, { db } from '@/classes/DB'
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

export function count({ where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: `${DB.TABLE.ARTICLE} AS a`,
    where: where || '',
    values: values || {},
  })
  return count.data || 0
}

export async function remove(srl: number)
{
  console.log('Article.helper.remove()', srl)
  // TODO: file data
  // TODO: comment data
  // TODO: tag data
  // TODO: article data
}
