import DB, { db } from '@/classes/DB'
import { compareIndex } from '@/libs/objects'
import type { BaseModel } from '@/libs/models'

export const MODULE = {
  ARTICLE: 'article',
  JSON: 'json',
  CHECKLIST: 'checklist',
}

export function getOriginTableName(module: string): string
{
  switch (module)
  {
    case MODULE.ARTICLE:
      return DB.TABLE.ARTICLE
    case MODULE.CHECKLIST:
      return DB.TABLE.CHECKLIST
    case MODULE.JSON:
      return DB.TABLE.JSON
    default:
      return ''
  }
}

export function count({ table, where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: table ?? DB.TABLE.MAP_TAG,
    where: where ?? '',
    values: values ?? {},
  })
  return count.data || 0
}

export function getIndex(module: string, module_srl: number)
{
  const tags = db.getIndex({
    table: `${DB.TABLE.TAG} as t`,
    field: `t.srl,t.name`,
    join: `JOIN ${DB.TABLE.MAP_TAG} AS mt ON mt.tag_srl = t.srl`,
    where: [
      `AND mt.module LIKE \'${module}\'`,
      `AND mt.module_srl = ${module_srl}`,
    ],
  })
  return tags.data.length > 0 ? tags.data : []
}

// TODO: 이 함수는 안쓰게 될거같다. update()로도 충분해 보인다.
export function add({ module, module_srl, tag }: ZZ)
{
  // get tag data
  const _tag = db.getData({
    table: DB.TABLE.TAG,
    field: 'srl,name',
    where: `name GLOB \'${tag}\'`,
  })
  let _tagSrl = _tag.data?.srl as number
  // add tag data
  if (!_tagSrl)
  {
    const _tag = db.addData({
      table: DB.TABLE.TAG,
      values: [{ key: 'name', value: tag }],
    })
    _tagSrl = _tag.data as number
  }
  if (!_tagSrl) return
  // add mapping tag data
  const _count = db.getCount({
    table: DB.TABLE.MAP_TAG,
    where: [
      `AND module LIKE \'${module}\'`,
      `AND module_srl = ${module_srl}`,
      `AND tag_srl = ${_tagSrl}`,
    ],
  })
  if (_count.data <= 0)
  {
    const _added = db.addData({
      table: DB.TABLE.MAP_TAG,
      values: [
        { key: 'tag_srl', value: _tagSrl },
        { key: 'module', value: module },
        { key: 'module_srl', value: module_srl },
      ],
    })
    return _added.data
  }
}

export function update({ module, module_srl, tags }: ZZ)
{
  // get old tags
  const oldTags = db.getIndex({
    table: `${DB.TABLE.TAG} as t`,
    field: `t.name`,
    join: `JOIN ${DB.TABLE.MAP_TAG} as mt ON mt.tag_srl = t.srl`,
    where: [
      `AND module LIKE \'${module}\'`,
      `AND module_srl = ${module_srl}`,
    ],
  }).data?.map((o: ZZ) => (o.name)) || []
  // compare tags
  const compare = compareIndex(oldTags, tags)
  // remove tags
  if (compare.removed.length > 0)
  {
    for (const tag of compare.removed)
    {
      removeTag({ module, module_srl, tag })
    }
  }
  // add tags
  if (compare.added.length > 0)
  {
    for (const tag of compare.added)
    {
      add({ module, module_srl, tag })
    }
  }
}

export function remove({ module, module_srl }: ZZ)
{
  // get mapping data
  const _mapTagSrls = db.getIndex({
    table: DB.TABLE.MAP_TAG,
    field: 'tag_srl',
    where: [
      `AND module LIKE \'${module}\'`,
      `AND module_srl = ${module_srl}`,
    ],
  }).data?.map(({ tag_srl }: ZZ) => (tag_srl))
  // delete mapping data
  db.deleteData({
    table: DB.TABLE.MAP_TAG,
    where: [
      `AND module LIKE \'${module}\'`,
      `AND module_srl = ${module_srl}`,
    ],
  })
  // delete tag data
  for (const _srl of _mapTagSrls)
  {
    const _count = db.getCount({
      table: DB.TABLE.MAP_TAG,
      where: `tag_srl = ${_srl}`,
    })
    if (_count.data <= 0)
    {
      db.deleteData({
        table: DB.TABLE.TAG,
        where: `srl = ${_srl}`,
      })
    }
  }
}

export function removeTag({ module, module_srl, tag }: ZZ)
{
  // get tag srl
  const _tag = db.getData({
    table: DB.TABLE.TAG,
    field: 'srl',
    where: `name GLOB \'${tag}\'`,
  })
  const _tagSrl = _tag.data?.srl as number
  if (!_tagSrl) return
  // delete data from mapping table
  db.deleteData({
    table: DB.TABLE.MAP_TAG,
    where: [
      `AND module LIKE \'${module}\'`,
      `AND module_srl = ${module_srl}`,
      `AND tag_srl = ${_tagSrl}`,
    ],
  })
  // get count data from mapping table
  const _count = db.getCount({
    table: DB.TABLE.MAP_TAG,
    where: `tag_srl = ${_tagSrl}`,
  })
  // delete data from tag table
  if (_count.data <= 0)
  {
    db.deleteData({
      table: DB.TABLE.TAG,
      where: `srl = ${_tagSrl}`,
    })
  }
}
