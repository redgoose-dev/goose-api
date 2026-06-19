import DB, { db } from '@/classes/DB'
import type { BaseModel } from '@/libs/models'

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
  // TODO: 아티클, 태그, 파일, 댓글 삭제
  console.log('Article.helper.remove()', srl)
}
