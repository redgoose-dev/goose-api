import { Elysia, t } from 'elysia'
import { App } from './service'
import { AppModel } from './model'

const route = new Elysia({
  prefix: '/app',
})

// 🌿 Index
route.get('/', async (ctx) => {
  // const { request } = ctx
  return await App.getIndex()
}, {
  // query: t.Object({}),
})

// 🌻 Detail app
route.get('/:srl/', async (ctx) => {
  const { params } = ctx
  return await App.getItem(params.srl)
}, {
  params: AppModel.getItemParams,
})

// 🌱 Create app
route.put('/', async (ctx) => {
  const { request, body } = ctx
  // TODO: 토큰 검사
  // put item data
  const addedSrl = await App.putItem({ request, body })
  // return response
  return {
    message: 'Success add data.',
    data: addedSrl,
  }
}, {
  body: AppModel.putItemBody,
})
// TODO

// 🌳 Update app
// TODO

// 🍄 Delete app
// TODO

export default route
