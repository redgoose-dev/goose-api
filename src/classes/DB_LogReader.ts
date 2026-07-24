import { Database } from 'bun:sqlite'
import { existsSync } from 'node:fs'
import type { SQLQueryBindings } from 'bun:sqlite'

/**
 * 로그 데이터베이스 조회 전용 연결.
 *
 * 기록용 DB_Log와 연결을 공유하지 않으며, SQL의 의미는 해석하지 않는다.
 * 조회할 파일이나 log 테이블이 없으면 단건은 null, 목록은 빈 배열을 반환한다.
 */
export default class DB_LogReader {

  readonly path: string

  #db?: Database

  constructor(path: string)
  {
    if (!path) throw new Error('Log database path is required.')
    this.path = path
  }

  #connect(): Database | undefined
  {
    if (this.#db) return this.#db
    if (!existsSync(this.path)) return undefined
    let database: Database
    try
    {
      database = new Database(this.path, {
        readonly: true,
        strict: true,
      })
    }
    catch (error)
    {
      if (!existsSync(this.path)) return undefined
      throw error
    }
    database.exec('PRAGMA query_only = ON')
    database.exec('PRAGMA busy_timeout = 1000')
    const table = database.query(`SELECT 1 AS found FROM sqlite_master WHERE type = 'table' AND name = 'log'`).get()
    if (!table)
    {
      database.close()
      return undefined
    }
    this.#db = database
    return database
  }

  getData<T>(sql: string, values?: SQLQueryBindings): T | null
  {
    const database = this.#connect()
    if (!database) return null
    const query = database.query(sql)
    return (values === undefined ? query.get() : query.get(values)) as T | null
  }

  getIndex<T>(sql: string, values?: SQLQueryBindings): T[]
  {
    const database = this.#connect()
    if (!database) return []
    const query = database.query(sql)
    return (values === undefined ? query.all() : query.all(values)) as T[]
  }

  transaction<T>(callback: () => T): T | undefined
  {
    const database = this.#connect()
    if (!database) return undefined
    return database.transaction(callback).deferred()
  }

  close(): void
  {
    if (!this.#db) return
    this.#db.close()
    this.#db = undefined
  }
}
