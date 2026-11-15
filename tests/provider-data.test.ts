import { afterAll, expect, test } from 'bun:test'
import { Database } from 'bun:sqlite'
import { mkdtempSync, rmSync } from 'node:fs'
import { join } from 'node:path'
import { migrateProvider } from '../resource/migrate-provider'
import DB, { db } from '../src/classes/DB'
import putProviderItem from '../src/routes/auth/put-provider-item'
import patchProviderItem from '../src/routes/auth/patch-provider-item'
import postLogin from '../src/routes/auth/post-login'
import getProviderItem from '../src/routes/auth/get-provider-item'
import { getPublicProvider } from '../src/routes/auth/__helper'
import type { CheckinToken } from '../src/libs/verify'

const seed = await Bun.file(new URL('../resource/seed.sql', import.meta.url)).text()
const directory = mkdtempSync(join(import.meta.dir, 'provider-test-'))
const originalPath = DB.PATH
const envKeys = [ 'AUTH_PASSWORD_ACCESS_SECRET', 'AUTH_PASSWORD_REFRESH_SECRET', 'AUTH_PASSWORD_ACCESS_EXPIRES', 'AUTH_PASSWORD_REFRESH_EXPIRES' ] as const
const originalEnv = envKeys.map(key => Bun.env[key])
DB.PATH = join(directory, 'db.sqlite')
const connection = new Database(DB.PATH, { create: true })
connection.run(seed)
connection.close()
Bun.env.AUTH_PASSWORD_ACCESS_SECRET = 'test-access'
Bun.env.AUTH_PASSWORD_REFRESH_SECRET = 'test-refresh'
Bun.env.AUTH_PASSWORD_ACCESS_EXPIRES = '1h'
Bun.env.AUTH_PASSWORD_REFRESH_EXPIRES = '1d'

afterAll(() => {
  db.close()
  DB.PATH = originalPath
  envKeys.forEach((key, index) => {
    if (originalEnv[index] === undefined) delete Bun.env[key]
    else Bun.env[key] = originalEnv[index]
  })
  rmSync(directory, { recursive: true, force: true })
})

test('new schema, password login, partial updates and response secrecy', async () => {
  await putProviderItem({ body: { id: 'tester', password: 'initial' } })
  const row = db.getData({ table: 'provider' }).data
  expect(row.uid).toBe('tester')
  expect(row.user_id).toBeUndefined()
  expect(JSON.parse(row.data)).not.toHaveProperty('name')
  expect(JSON.parse(row.data)).not.toHaveProperty('avatar')
  expect(JSON.parse(row.data).password).not.toBe('initial')
  db.run(`UPDATE provider SET data = json_set(data, '$.custom', json('{"keep":true}'))`)
  await patchProviderItem({ srl: row.srl, body: { id: 'tester', name: '' } })
  const updated = db.getData({ table: 'provider' }).data
  expect(JSON.parse(updated.data).custom).toEqual({ keep: true })
  expect(JSON.parse(updated.data).name).toBe('')
  const account = await getProviderItem({ srl: row.srl, token: {} as CheckinToken })
  expect(account.uid).toBe('tester')
  expect(account).not.toHaveProperty('user_password')
  expect(account).not.toHaveProperty('data')
  expect(JSON.stringify(account)).not.toContain(JSON.parse(updated.data).password)
  expect(JSON.stringify(account)).not.toContain('custom')
  expect((await postLogin({ body: { id: 'tester', password: 'initial' } })).access).toHaveLength(32)
  const token = db.getData({ table: 'token' }).data
  const payload = JSON.parse(Buffer.from(token.access.split('.')[1], 'base64url').toString())
  expect(payload.uid).toBe('tester')
  expect(payload).not.toHaveProperty('user_id')
  await expect(postLogin({ body: { id: 'tester', password: 'wrong' } })).rejects.toThrow()
  await patchProviderItem({ srl: row.srl, body: { id: 'renamed', password: 'changed' } })
  await expect(postLogin({ body: { id: 'renamed', password: 'initial' } })).rejects.toThrow()
  expect((await postLogin({ body: { id: 'renamed', password: 'changed' } })).access).toHaveLength(32)
  await expect(patchProviderItem({ srl: row.srl, body: {} })).rejects.toThrow()
  expect(() => db.run(`UPDATE provider SET data = '[]'`)).toThrow()
  const current = new Database(DB.PATH)
  try
  {
    expect(migrateProvider(current)).toBe(false)
  }
  finally
  {
    current.close()
  }
})

test('legacy migration preserves accounts, token references and repeatability', () => {
  const legacy = new Database(':memory:')
  try
  {
    legacy.run(`
      PRAGMA foreign_keys = ON;
      CREATE TABLE provider (srl INTEGER PRIMARY KEY AUTOINCREMENT, code TEXT NOT NULL, user_id TEXT NOT NULL,
        user_name TEXT, user_avatar TEXT, user_email TEXT, user_password TEXT, created_at TEXT NOT NULL, UNIQUE(code, user_id));
      CREATE INDEX idx_provider_user_id ON provider(user_id);
      CREATE TABLE token (srl INTEGER PRIMARY KEY, provider_srl INTEGER REFERENCES provider(srl) ON DELETE CASCADE);
      INSERT INTO provider VALUES (7, 'password', 'tester', '', NULL, 'mail', 'hash', 'date');
      INSERT INTO provider VALUES (8, 'google', 'oauth', 'OAuth', NULL, NULL, NULL, 'date');
      INSERT INTO token VALUES (3, 7);
    `)
    expect(migrateProvider(legacy)).toBe(true)
    expect(migrateProvider(legacy)).toBe(false)
    const row = legacy.query('SELECT * FROM provider WHERE srl = 7').get() as { srl: number, code: string, uid: string, data: string, created_at: string }
    expect(JSON.parse(row.data)).toEqual({ name: '', avatar: null, email: 'mail', password: 'hash' })
    expect(getPublicProvider(row).uid).toBe('tester')
    expect(legacy.query('SELECT * FROM token').all()).toEqual([{ srl: 3, provider_srl: 7 }])
    expect(legacy.query('PRAGMA foreign_key_check').all()).toEqual([])
    expect(() => legacy.run(`INSERT INTO provider(code, uid, created_at) VALUES ('password', 'tester', 'date')`)).toThrow()
  }
  finally
  {
    legacy.close()
  }
})

test('failed migration rolls back all schema and data changes', () => {
  const legacy = new Database(':memory:')
  try
  {
    legacy.run(`CREATE TABLE provider (srl INTEGER PRIMARY KEY, code TEXT, user_id TEXT, user_name TEXT, user_avatar TEXT, user_email TEXT, user_password TEXT, created_at TEXT);
      CREATE INDEX prevent_drop ON provider(user_name);
      INSERT INTO provider VALUES (1, 'password', 'id', 'name', NULL, NULL, 'hash', 'date');`)
    expect(() => migrateProvider(legacy)).toThrow()
    const row = legacy.query('SELECT * FROM provider').get() as Record<string, unknown>
    expect(row.user_password).toBe('hash')
    expect(row.data).toBeUndefined()
    expect(row.uid).toBeUndefined()
  }
  finally
  {
    legacy.close()
  }
})


test('optional OAuth profile and password-only credentials', async () => {
  db.run("INSERT INTO provider (code, uid, data, created_at) VALUES ('google', 'oauth', '{}', 'date')")
  const row = db.getData({ table: 'provider', where: "code = 'google'" }).data
  const account = await getProviderItem({ srl: row.srl, token: {} as CheckinToken })
  expect(account).toEqual({ srl: row.srl, code: 'google', uid: 'oauth', created_at: 'date' })
  await patchProviderItem({ srl: row.srl, body: { name: 'OAuth' } })
  await expect(patchProviderItem({ srl: row.srl, body: { password: 'invalid' } })).rejects.toMatchObject({ status: 400 })
  const data = JSON.parse(db.getData({ table: 'provider', where: "code = 'google'" }).data.data)
  expect(data).toEqual({ name: 'OAuth' })
  await expect(postLogin({ body: { id: 'oauth', password: 'invalid' } })).rejects.toMatchObject({ status: 401 })
})

test('password provider without a hash rejects login with 401', async () => {
  db.run("INSERT INTO provider (code, uid, data, created_at) VALUES ('password', 'nohash', '{}', 'date')")
  await expect(postLogin({ body: { id: 'nohash', password: 'invalid' } })).rejects.toMatchObject({ status: 401 })
})


test('public provider uses unprefixed profile fields', () => {
  const account = getPublicProvider({
    srl: 1,
    code: 'google',
    uid: 'profile',
    created_at: 'date',
    data: JSON.stringify({ name: 'Profile', email: 'profile@example.com', avatar: 'https://example.com/avatar.png', password: 'secret' }),
  })
  expect(account).toEqual({
    srl: 1,
    code: 'google',
    uid: 'profile',
    created_at: 'date',
    name: 'Profile',
    email: 'profile@example.com',
    avatar: 'https://example.com/avatar.png',
  })
})
