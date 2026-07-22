import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import MOD from '@/classes/MOD'
import { parseJSON } from '@/libs/objects'
import * as appHelper from '@/routes/app/__helper'
import * as nestHelper from '@/routes/nest/__helper'
import * as categoryHelper from '@/routes/category/__helper'
import * as tagHelper from '@/routes/tag/__helper'
import * as fileHelper from '@/routes/file/__helper'
import * as helper from './__helper'
import type { CheckinToken } from '@/libs/verify'
import type { ArticleModel } from './__model'

const DURATION_FIELDS = {
  regdate: { filter: 'a.regdate', order: 'a.regdate' },
  created_at: { filter: 'SUBSTR(a.created_at, 1, 10)', order: 'a.created_at' },
  updated_at: { filter: 'SUBSTR(a.updated_at, 1, 10)', order: 'a.updated_at' },
} as const

const DURATION_UNITS = {
  day: 'day',
  week: 'day',
  month: 'month',
  year: 'year',
} as const

const RANDOM_MODULUS = 2_147_483_647
const RANDOM_HASH_XOR = 0x9e3779b9
const RANDOM_HASH_MULTIPLIER = 0x85ebca6b
const RANDOM_HASH_MIX = 0xc2b2ae35
const RANDOM_OFFSET_MULTIPLIER = 1_664_525

function mixRandomSeed(seed: number)
{
  let value = (seed % 4_294_967_296) >>> 0
  value = Math.imul(value ^ RANDOM_HASH_XOR, RANDOM_HASH_MULTIPLIER)
  value = Math.imul(value ^ (value >>> 13), RANDOM_HASH_MIX)
  value ^= value >>> 16
  return value >>> 0
}

function getRandomOrder(seedValue: string)
{
  if (!/^[0-9]+$/.test(seedValue))
  {
    throw new ServiceError('Invalid random seed.', { status: 400 })
  }

  const seed = Number(seedValue)
  if (!Number.isSafeInteger(seed))
  {
    throw new ServiceError('Invalid random seed.', { status: 400 })
  }

  // Build a seed-dependent affine permutation within a large prime range.
  // The reduced article srl prevents multiplication from overflowing SQLite's integer range.
  const mixedSeed = mixRandomSeed(seed)
  const multiplier = (mixedSeed % (RANDOM_MODULUS - 1)) + 1
  const offset = (Math.imul(mixedSeed, RANDOM_OFFSET_MULTIPLIER) >>> 0) % RANDOM_MODULUS

  return {
    order: `(((a.srl % ${RANDOM_MODULUS}) * $randomMultiplier + $randomOffset) % ${RANDOM_MODULUS}), a.srl ASC`,
    values: {
      '$randomMultiplier': multiplier,
      '$randomOffset': offset,
    },
  }
}

type GetIndexParams = {
  query: ArticleModel['getIndexQuery']
  token: CheckinToken
  service: Service
}

export default async function getIndex({ query, token, service }: GetIndexParams)
{
  try
  {
    // set assets
    let _table = `${DB.TABLE.ARTICLE} AS a`
    let _where: string[] = []
    let _values: ZZ = {}
    let _join: string[] = []
    let _order: string | undefined
    let _durationOrder: string | undefined
    let _field = query.field ? query.field.split(',') : []

    // set base params
    if (query.app !== undefined)
    {
      _join.push(`JOIN ${DB.TABLE.NEST} AS n ON n.srl = a.nest_srl`)
      _where.push(`AND n.app_srl = ${query.app}`)
    }
    if (query.nest !== undefined)
    {
      _where.push(`AND nest_srl = ${query.nest}`)
    }
    if (query.category !== undefined)
    {
      if (query.category > 0) _where.push(`AND category_srl = ${query.category}`)
      else _where.push(`AND category_srl IS NULL`)
    }
    if (query.q !== undefined)
    {
      _where.push(`AND (a.title LIKE \'%${query.q}%\' OR content LIKE \'%${query.q}%\')`)
    }
    if (token.public)
    {
      _where.push(`AND mode LIKE \'${helper.STATUS.PUBLIC}\'`)
    }
    else if (query.mode)
    {
      _where.push(`AND mode LIKE \'${query.mode}\'`)
    }
    else
    {
      _where.push(`AND mode NOT LIKE \'${helper.STATUS.READY}\'`)
    }
    if (query.tag !== undefined)
    {
      const _tags = query.tag.split(',').join(',')
      _where.push(`AND a.srl IN (SELECT mt.module_srl FROM ${DB.TABLE.MAP_TAG} AS mt WHERE mt.module LIKE \'${tagHelper.MODULE.ARTICLE}\' AND mt.tag_srl IN (${_tags}))`)
    }
    if (query.duration !== undefined)
    {
      const _duration = /^(regdate|created_at|updated_at),([1-9][0-9]*)(day|week|month|year)$/.exec(query.duration)
      if (!_duration) throw new ServiceError('Invalid duration.', { status: 400 })
      const [, _fieldName, _amountText, _unit] = _duration
      const _field = DURATION_FIELDS[_fieldName as keyof typeof DURATION_FIELDS]
      const _sqlUnit = DURATION_UNITS[_unit as keyof typeof DURATION_UNITS]
      const _amount = Number(_amountText)
      const _sqlAmount = _unit === 'week' ? _amount * 7 : _amount
      if (!_field || !_sqlUnit || !Number.isSafeInteger(_amount) || !Number.isSafeInteger(_sqlAmount))
      {
        throw new ServiceError('Invalid duration.', { status: 400 })
      }
      const _modifier = `-${_sqlAmount} ${_sqlUnit}`
      _where.push(`AND ${_field.filter} IS NOT NULL AND ${_field.filter} <= STRFTIME('%Y-%m-%d', 'now', 'localtime', '${_modifier}')`)
      _durationOrder = `${_field.order} DESC, a.srl DESC`
    }

    // get count
    const count = db.getCount({
      table: _table,
      field: 'COUNT(DISTINCT a.srl) AS count',
      where: _where,
      join: _join,
      values: _values,
    })
    if (count.data <= 0) throw new ServiceError('No data', { status: 204 })

    // set random
    if (query.random)
    {
      const _random = getRandomOrder(query.random)
      _order = _random.order
      _values = {
        ..._values,
        ..._random.values,
      }
    }
    else if (query.order)
    {
      _order = query.order ?? ''
    }
    else if (_durationOrder)
    {
      _order = _durationOrder
    }

    // get index
    let index = db.getIndex({
      table: _table,
      prefix: 'DISTINCT',
      field: _field,
      where: _where,
      join: _join,
      order: _order,
      page: query.page ?? 1,
      size: query.size ?? service.preference['article.index.size'],
      values: _values,
    })

    // set MOD
    const _mod: MOD = new MOD(query.mod)

    // transform index
    index.data = index.data.map((item: ZZ) => {
      if (item.json) item.json = parseJSON(item.json)
      // MOD / app
      if (_mod.check('app') && item.nest_srl)
      {
        item.app = appHelper.getItemFromNest(item.nest_srl, 'srl,code,name')
      }
      // MOD / nest
      if (_mod.check('nest') && item.nest_srl)
      {
        item.nest = nestHelper.getItem(item.nest_srl, 'srl,code,name')
      }
      // MOD / category
      if (_mod.check('category') && item.category_srl)
      {
        item.category = categoryHelper.getItem(item.category_srl, 'srl,name')
      }
      // MOD / tag
      if (_mod.check('tag'))
      {
        item.tag = tagHelper.getIndex(tagHelper.MODULE.ARTICLE, item.srl)
      }
      // MOD / file
      if (_mod.check('file'))
      {
        item.file = fileHelper.getIndex({
          module: fileHelper.MODULE.ARTICLE,
          module_srl: item.srl,
          field: 'srl,code,name',
        })
      }
      return item
    })

    return {
      total: count.data,
      index: index.data,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to get Article index.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
