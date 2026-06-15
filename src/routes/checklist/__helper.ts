import DB, { db } from '@/classes/DB'
import type { BaseModel } from '@/libs/models'

export function filteringContent(str: string): string
{
  return str.replace(/'/g, "\\'")
}

export function getPercentIntoChecks(body: string): number
{
  if (!body) return 0
  const total = (body.match(/- \[x\]|- \[ \]/g) ?? []).length
  const checked = (body.match(/- \[x\]/g) ?? []).length
  if (!(total > 0 && checked > 0)) return 0
  return Math.floor((checked / total) * 100)
}

export function count(op: BaseModel['paramsTableSelect'])
{
  const count = db.getCount({
    table: DB.TABLE.CHECKLIST,
    where: op.where || '',
    values: op.values || {},
  })
  return count.data || 0
}
