import { Elysia } from 'elysia'
import { Home } from './service'
import { HomeModel } from './model'

const routes = new Elysia({
  prefix: '/',
})

// 🌻 Service info
routes.get('/', (ctx) => {
  const { store } = ctx as Context
  return Home.getInfo(store.service)
}, {
  response: HomeModel.response,
})

export default routes
