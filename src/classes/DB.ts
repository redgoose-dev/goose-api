import { Database } from 'bun:sqlite'
import type { ParamAddData, ParamGetCount, ParamGetIndex, ParamGetData, ReturnData, ParamLimit, ParamRunValues, ParamPatchData, ParamDeleteData } from './DB.types'
import { PATHS } from '@/libs/assets'
import debug from '@/libs/debug'

/**
 * DB 파일 크기 기반 PRAGMA 값 캐시
 * 매 요청마다 파일 stat을 하지 않도록 일정 시간 간격으로만 갱신한다.
 */
let _pragmaCache = { mmap: 0, cache: 0, updatedAt: 0 }
const PRAGMA_TTL = 1000 * 60 * 60 // 1시간마다 갱신

class DB {

  static PATH = `${PATHS.DATA}/db.sqlite`
  static TABLE = {
    APP: 'app',
    ARTICLE: 'article',
    CATEGORY: 'category',
    CHECKLIST: 'checklist',
    COMMENT: 'comment',
    FILE: 'file',
    JSON: 'json',
    NEST: 'nest',
    PROVIDER: 'provider',
    TAG: 'tag',
    MAP_TAG: 'map_tag',
    TOKEN: 'token',
  }
  static DATE_TIME = 'DATETIME("now", "localtime")'
  private conn?: Database

  #connect(): Database
  {
    if (!this.conn)
    {
      this.conn = new Database(DB.PATH, { readwrite: true })
      this.conn.run('PRAGMA journal_mode = WAL')
      this.conn.run('PRAGMA synchronous = NORMAL')
      const { mmap, cache } = getPragmaValues()
      this.conn.run(`PRAGMA mmap_size = ${mmap}`)
      this.conn.run(`PRAGMA cache_size = ${cache}`)
      this.conn.run('PRAGMA temp_store = MEMORY')
    }
    return this.conn
  }

  #optimizeSql(str: string): string
  {
    return str.trim().replace(/\s{2,}/g, ' ')
  }

  #parseJoin(src?: string | string[]): string
  {
    if (Array.isArray(src))
    {
      return src.map(x => (x)).join(' ')
    }
    else if (typeof src === 'string')
    {
      return src
    }
    return ''
  }

  #getField(src?: string | string[]): string
  {
    if (Array.isArray(src))
    {
      return src.map(x => (x)).join(',') || '*'
    }
    else if (typeof src === 'string')
    {
      return src || '*'
    }
    return '*'
  }

  #getWhere(str?: string | string[]): string
  {
    if (!str) return ''
    let _str = Array.isArray(str) ? str.join(' ') : str
    _str = _str.trim().replace(/^(and|or|AND|OR)/, '').trim()
    return _str ? `WHERE ${_str}` : ''
  }

  #getLimit(op: ParamLimit = {}): string
  {
    if (op.page === undefined || op.size === undefined) return ''
    if (op.page <= 0 || op.size <= 0) return ''
    let _size = op.size || 24
    let _offset = ((op.page || 1) - 1) * _size
    return `LIMIT ${_size} OFFSET ${_offset}`
  }

  #getOrder(order?: string, sort?: string): string
  {
    if (!(order && sort)) return ''
    const _order = order || 'srl'
    const _sort = sort || 'desc'
    return `ORDER BY ${_order} ${_sort}`
  }

  #getSet(arr: Array<string | boolean>): string
  {
    return (arr?.length > 0) ? arr.filter(Boolean).join(', ') : ''
  }

  #debug(name: string, sql?: string, values?: ZZ)
  {
    debug(`DB.method:${name}`, { sql, values })
  }

  run(sql: string, values?: ParamRunValues)
  {
    if (values === undefined)
    {
      this.#connect().run(sql)
    }
    else
    {
      this.#connect().run(sql, values || {})
    }
  }

  transaction(mode: 'begin'|'commit'|'rollback', inTransaction?: boolean): boolean
  {
    switch (mode)
    {
      case 'begin':
        this.run('BEGIN TRANSACTION')
        return true
      case 'commit':
        this.run('COMMIT')
        return false
      case 'rollback':
        if (inTransaction)
        {
          try
          {
            this.run('ROLLBACK')
          }
          catch(_e)
          {}
        }
        return false
    }
  }

  getCount(op: ParamGetCount): { data: number } & ReturnData
  {
    const _field = op.field ? this.#getField(op.field) : 'COUNT(*) AS count'
    const _join = this.#parseJoin(op.join)
    const _where = this.#getWhere(op.where)
    const _sql = this.#optimizeSql(`SELECT ${op.prefix || ''} ${_field} FROM ${op.table} ${_join} ${_where} ${op.after || ''}`)
    if (op.debug) this.#debug('DB.getCount()', _sql, op.values)
    let _data = 0
    if (op.run !== false)
    {
      const _query = this.#connect().query(_sql)
      const result = _query.get(op.values) as ZZ
      _data = Number(result['count'] || 0)
    }
    return {
      sql: _sql,
      values: op.values,
      data: _data,
    }
  }

  getIndex(op: ParamGetIndex): { data: ZZ[] } & ReturnData
  {
    const _field = this.#getField(op.field)
    const _join = this.#parseJoin(op.join)
    const _where = this.#getWhere(op.where)
    const _limit = this.#getLimit({ page: op.page, size: op.size })
    const _order = this.#getOrder(op.order, op.sort)
    const _sql = this.#optimizeSql(`SELECT ${op.prefix || ''} ${_field} FROM ${op.table} ${_join} ${_where} ${_order} ${_limit}`)
    if (op.debug) this.#debug('getIndex()', _sql, op.values)
    let _data: ZZ[] = []
    if (op.run !== false)
    {
      const _query = this.#connect().query(_sql)
      _data = _query.all(op.values) as ZZ[]
    }
    return {
      sql: _sql,
      values: op.values,
      data: _data,
    }
  }

  getData(op: ParamGetData): { data: ZZ } & ReturnData
  {
    const _field = this.#getField(op.field)
    const _join = this.#parseJoin(op.join)
    const _where = this.#getWhere(op.where)
    const _sql = this.#optimizeSql(`SELECT ${_field} FROM ${op.table} ${_join} ${_where}`)
    if (op.debug) this.#debug('DB.getData()', _sql, op.values)
    let _data
    if (op.run !== false)
    {
      const _query = this.#connect().query(_sql)
      _data = _query.get(op.values)
    }
    return {
      sql: _sql,
      values: op.values,
      data: _data as ZZ,
    }
  }

  addData(op: ParamAddData): ReturnData
  {
    let fields: string[] = []
    let valueNames: string[] = []
    let values: string[] = []
    op.values.forEach((item: ZZ) => {
      if (!item) return
      if (item.value || item.valueName)
      {
        fields.push(item.key)
        valueNames.push(item.valueName || '?')
      }
      if (item.value) values.push(item.value)
    })
    const _sql = this.#optimizeSql(`INSERT INTO ${op.table} (${fields.join(', ')}) VALUES (${valueNames.join(', ')})`)
    if (op.debug) this.#debug('DB.addData()', _sql, op.values)
    let _data
    if (op.run !== false)
    {
      this.#connect().run(_sql, values ?? {})
      _data = this.getLastKey(op.table)
    }
    return {
      sql: _sql,
      values: op.values,
      data: _data,
    }
  }

  editData(op: ParamPatchData): ReturnData
  {
    const _set = this.#getSet(op.set)
    const _where = this.#getWhere(op.where)
    let _sql = this.#optimizeSql(`UPDATE ${op.table} SET ${_set} ${_where}`)
    if (op.debug) this.#debug('DB.editData()', _sql, op.values)
    if (op.run !== false) this.#connect().run(_sql, op.values ?? {})
    return {
      sql: _sql,
      values: op.values,
    }
  }

  deleteData(op: ParamDeleteData): ReturnData
  {
    const _where = this.#getWhere(op.where)
    let _sql = this.#optimizeSql(`DELETE FROM ${op.table} ${_where}`)
    if (op.debug) this.#debug('DB.deleteData()', _sql, op.values)
    if (op.run !== false) this.#connect().run(_sql, op.values ?? {})
    return {
      sql: _sql,
      values: op.values,
    }
  }

  getLastKey(table: string): number
  {
    const sql = `SELECT max(srl) AS maxID FROM ${table}`
    const query = this.#connect().query(sql)
    return (query.get() as ZZ).maxID || 0
  }

  close()
  {
    this.conn?.close()
    this.conn = undefined
  }

}

function getPragmaValues()
{
  const now = Date.now()
  if (now - _pragmaCache.updatedAt < PRAGMA_TTL) return _pragmaCache
  const dbSize = Bun.file(DB.PATH).size
  _pragmaCache = {
    mmap: Math.ceil(dbSize * 3),
    cache: -Math.ceil((dbSize * 3) / 1024), // 음수 = KB 단위
    updatedAt: now,
  }
  return _pragmaCache
}

export default DB
export const db: InstanceType<typeof DB> = new DB()
