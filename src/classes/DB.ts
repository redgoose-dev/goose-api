import { Database } from 'bun:sqlite'
import type {ParamAddData, ParamGetCount, ReturnData} from './DB.types'
import { PATHS } from '@/libs/assets'

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

  #getWhere(str: string): string
  {
    return str.trim().replace(/^(and|or|AND|OR)/, ' ')
  }

  getCount(op: ParamGetCount): ReturnData
  {
    let field = op.field || 'COUNT(*) AS count'
    const sql = this.#optimizeSql(`SELECT ${op.prefix || ''} ${field} FROM ${op.table} ${this.#parseJoin(op.join)} ${op.where ? `WHERE ${this.#getWhere(op.where)}` : ''} ${op.after || ''}`)
    if (op.debug) console.warn('DB.getCount():', sql)
    let data = 0
    if (op.run !== false)
    {
      const query = this.#connect().query(sql)
      const result: any = query.get(op.values)
      data = Number(result['count'] || 0)
    }
    return {
      sql,
      data,
    }
  }

  getIndex(): ReturnData
  {
    return {
      sql: '',
      data: {},
    }
  }

  getData()
  {}

  addData(op: ParamAddData): ReturnData
  {
    let fields: string[] = []
    let valueNames: string[] = []
    let values: string[] = []
    op.values.forEach((item: ZZ) => {
      if (item.value || item.valueName)
      {
        fields.push(item.key)
        valueNames.push(item.valueName || '?')
      }
      if (item.value) values.push(item.value)
    })
    const sql = this.#optimizeSql(`INSERT INTO ${op.table} (${fields.join(', ')}) VALUES (${valueNames.join(', ')})`)
    if (op.debug) console.warn('DB.addData():', sql)
    if (op.run !== false)
    {
      this.#connect().run(sql, values)
    }
    return {
      sql,
      data: this.getLastKey(op.table),
    }
  }

  editData()
  {
    // TODO
  }

  removeData()
  {
    // TODO
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
