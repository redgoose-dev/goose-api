import { describe, expect, it, beforeAll, afterAll } from 'bun:test'
import { createTestApp, type ElysiaService } from './helpers/create-test-app'
import { createRequest } from './helpers/request-assets'
import routePreference from '@/routes/preference/__index'

/**
 * # Command guide
 * bun test preference --test-name-pattern "GET /preference/"
 */
describe('GET /preference/', () => {
  it('환경설정 데이터를 가져온다.', async () => {
    // ceate app
    const app = await createTestApp()
    app.use(routePreference)
    // call request
    const res = await app.handle(createRequest('/preference/'))
    expect(res.status).toBe(200)
    const data = await res.json()
    expect(data).toEqual(
      expect.objectContaining({
        message: expect.any(String),
        data: expect.any(Object),
      })
    )
  })
})

/**
 * # Command guide
 * bun test preference --test-name-pattern "PATCH /preference/"
 */
describe('PATCH /preference/', () => {
  let app: ElysiaService
  let origin: ZZ

  beforeAll(async () => {
    // ceate app
    app = await createTestApp()
    app.use(routePreference)
    // get origin preference
    const { service } = app.store
    origin = await service.loadPreference()
    expect(origin).toEqual(expect.anything())
  })
  afterAll(async () => {
    const { service } = app.store
    await service.updatePreference(origin, true)
  })

  it('환경설정 데이터를 업데이트한다.', async () => {
    const res = await app.handle(createRequest('/preference/', {
      method: 'PATCH',
      body: JSON.stringify({
        json: '{"foo": "bar", "article.pageSize": 30, "ROO": "BARRRR"}',
        change: false,
      })
    }))
    const { service } = app.store
    const data = await service.loadPreference()
    // console.log('[UPDATED DATA]', data)
    expect(res.status).toBe(200)
    expect(data).toEqual(expect.any(Object))
  })
  it('환경설정 데이터를 교체한다.', async () => {
    const res = await app.handle(createRequest('/preference/', {
      method: 'PATCH',
      body: JSON.stringify({
        json: '{"foo":"bar", "action":"CHANGE DATA."}',
        change: true,
      })
    }))
    const { service } = app.store
    const data = await service.loadPreference()
    // console.log('[CHANGED DATA]', data)
    expect(res.status).toBe(200)
    expect(data).toEqual(expect.any(Object))
  })
})
