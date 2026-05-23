import { Elysia, t } from 'elysia'
import { App } from './service'
import { AppModel } from './model'

const routes = new Elysia({
  prefix: '/app',
})

// 🌿 Index
routes.get('/', async (ctx) => {
  // const { request } = ctx
  return await App.getIndex()
}, {
  // query: t.Object({}),
})

// 🌻 Detail data
routes.get('/:srl/', async (ctx) => {
  const { params } = ctx
  return await App.getItem(params.srl)
}, {
  params: AppModel.params,
})

// 🌱 Create data
// TODO

// 🌳 Update data
// TODO

// 🍄 Delete Data
// TODO

export default routes
