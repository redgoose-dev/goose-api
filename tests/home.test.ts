import { describe, expect, it } from 'bun:test'
import { createTestApp } from './helpers/create-test-app'
import routeHome from '@/routes/home'

describe('GET /', () => {
  it('홈 엔드포인트가 응답한다', async () => {
    const app = await createTestApp()
    app.use(routeHome)

    const { service } = app.store

    const response = await app.handle(new Request('http://localhost/'))
    const data = await response.json()

    expect(response.status).toBe(200)
    expect(data).toEqual({
      message: `Hello! ${service.serviceName}`,
      version: service.version,
      dev: service.dev,
    })
  })
})

