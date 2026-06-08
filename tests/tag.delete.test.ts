import { describe, expect, it } from 'bun:test'
import { createTestApp } from './helpers/create-test-app'
import { createRequest } from './helpers/request-assets'
import routeTag from '@/routes/tag'
import DB, { db } from '@/classes/DB'

const MODULE = 'json'
const MODULE_SRL = 1
const TAG_NAME = ''

describe('DELETE /tag/', () => {
  it('임시로 추가한 태그를 삭제한다', async () => {
    const app = await createTestApp()
    app.use(routeTag)

    // 임시 태그 만들기
    const tagName = TAG_NAME || `test-delete-${Date.now()}`
    const tagSrl = Number(db.addData({
      table: DB.TABLE.TAG,
      values: [
        { key: 'name', value: tagName },
      ],
    }).data)
    db.addData({
      table: DB.TABLE.MAP_TAG,
      values: [
        { key: 'tag_srl', value: tagSrl },
        { key: 'module', value: MODULE },
        { key: 'module_srl', value: MODULE_SRL },
      ],
    })
    expect(tagSrl).toBeGreaterThan(0)
    expect(db.getCount({
      table: DB.TABLE.MAP_TAG,
      where: [
        `AND module LIKE '${MODULE}'`,
        `AND module_srl = ${MODULE_SRL}`,
        `AND tag_srl = ${tagSrl}`,
      ],
    }).data).toBe(1)

    try
    {
      const res = await app.handle(createRequest('/tag/', {
        method: 'DELETE',
        body: JSON.stringify({
          module: MODULE,
          module_srl: MODULE_SRL,
        }),
      }))
      expect(res.status).toBe(200)
      expect(db.getCount({
        table: DB.TABLE.MAP_TAG,
        where: [
          `AND module LIKE '${MODULE}'`,
          `AND module_srl = ${MODULE_SRL}`,
          `AND tag_srl = ${tagSrl}`,
        ],
      }).data).toBe(0)
      expect(db.getCount({
        table: DB.TABLE.TAG,
        where: `srl = ${tagSrl}`,
      }).data).toBe(0)
    }
    finally
    {
      db.deleteData({
        table: DB.TABLE.MAP_TAG,
        where: [
          `AND module LIKE '${MODULE}'`,
          `AND module_srl = ${MODULE_SRL}`,
          `AND tag_srl = ${tagSrl}`,
        ],
      })
      db.deleteData({
        table: DB.TABLE.TAG,
        where: `srl = ${tagSrl}`,
      })
    }
  })
})
