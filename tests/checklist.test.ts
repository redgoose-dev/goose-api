import { describe, expect, it, beforeAll } from 'bun:test'
import DB, { db } from '@/classes/DB'
import { createTestApp, type ElysiaService } from './helpers/create-test-app'
import { createRequest } from './helpers/request-assets'
import * as routes from '@/routes/index'
import { dateFormat } from '@/libs/strings'
import { dconsole, getData } from './helpers/debug'
import * as tagHelper from '@/routes/tag/__helper'

/**
 * # Command guide
 * bun test preference --test-name-pattern "GET /checklist/"
 */
describe('GET /checklist/', () => {
  let app: ElysiaService
  beforeAll(async () => {
    app = await createTestApp()
    app.use(routes.checklist)
  })
  it.only('체크리스트 목록을 가져온다.', async () => {
    const res = await app.handle(createRequest(`/checklist/`, {
      method: 'GET',
      query: {
        // content: 'updated',
        // tag: '2',
        // mod: 'tag',
        size: 2,
        page: 1,
      },
    }))
    expect(res.status).toBe(200)
    const data = await getData(res)
    expect(data).toEqual(
      expect.objectContaining({
        message: expect.any(String),
        data: expect.any(Object),
      })
    )
    // dconsole('[RESULT]', data)
  })
})

/**
 * # Command guide
 * bun test preference --test-name-pattern "GET /checklist/:srl/"
 */
describe('GET /checklist/:srl/', () => {
  const srl = 1
  let app: ElysiaService
  beforeAll(async () => {
    app = await createTestApp()
    app.use(routes.checklist)
  })
  it.only('체크리스트 하나를 가져온다.', async () => {
    const res = await app.handle(createRequest(`/checklist/${srl}/`, {
      method: 'GET',
      query: {
        field: 'srl,content,percent',
        mod: 'tag,count-file',
      },
    }))
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
 * bun test preference --test-name-pattern "PUT /checklist/"
 */
describe('PUT /checklist/', () => {
  let app: ElysiaService
  beforeAll(async () => {
    app = await createTestApp()
    app.use(routes.checklist)
  })
  it.only('체크리스트 하나를 등록한다.', async () => {
    const res = await app.handle(createRequest('/checklist/', {
      method: 'PUT',
      body: JSON.stringify({
        content: '- [ ] Content item #1\n- [x] Content item #2\n- [ ] Content item #3\n',
        regdate: dateFormat(new Date(), '{yyyy}-{MM}-{dd} {hh}:{mm}:{ss}'),
        tag: 'foo,bar',
      }),
    }))
    expect(res.status).toBe(200)
    const data = await res.json()
    expect(data).toEqual(
      expect.objectContaining({
        message: expect.any(String),
        data: expect.any(Number),
      })
    )
  })
})

/**
 * # Command guide
 * bun test preference --test-name-pattern "PATCH /checklist/:srl/"
 */
describe('PATCH /checklist/:srl/', () => {
  const updateSrl = 2
  let app: ElysiaService
  beforeAll(async () => {
    app = await createTestApp()
    app.use(routes.checklist)
  })
  it.only('체크리스트 하나를 업데이트한다.', async () => {
    const _body = {
      content: '- [ ] Updated Content item #1\n- [x] Updated Content item #2\n- [x] Updated Content item #3',
      tag: '123,456,789',
      // tag: '',
    }
    const res = await app.handle(createRequest(`/checklist/${updateSrl}/`, {
      method: 'PATCH',
      body: JSON.stringify(_body),
    }))
    const data = await res.text()
    expect(res.status).toBe(200)
    expect(data).toEqual(expect.any(String))
  })
})

/**
 * # Command guide
 * bun test preference --test-name-pattern "DELETE /checklist/:srl/"
 */
describe('DELETE /checklist/:srl/', () => {
  let app: ElysiaService
  beforeAll(async () => {
    app = await createTestApp()
    app.use(routes.checklist)
  })
  it.only('체크리스트를 삭제한다.', async () => {
    // 삭제할 체크리스트 만들기
    const addedRes = await app.handle(createRequest('/checklist', {
      method: 'PUT',
      body: JSON.stringify({
        content: '- [ ] item #1\n- [x] item #2',
        tag: 'EOOO,FOOO,HOOO',
      }),
    }))
    const checklistSrl = (await getData(addedRes)).data as number
    expect(checklistSrl).toBeGreaterThan(0)
    try
    {
      // 체크리스트 삭제하기
      const res = await app.handle(createRequest(`/checklist/${checklistSrl}/`, {
        method: 'DELETE',
      }))
      expect(res.status).toBe(200)
      const data = await getData(res)
      expect(data).toEqual(expect.any(String))
    }
    finally
    {
      db.deleteData({
        table: DB.TABLE.CHECKLIST,
        where: `srl = ${checklistSrl}`,
      })
      tagHelper.remove({
        module: tagHelper.MODULE.CHECKLIST,
        module_srl: checklistSrl,
      })
    }
  })
})
