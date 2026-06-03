import { Elysia } from 'elysia'
import { Home } from './service'
import { HomeModel } from './model'

const route = new Elysia()

// 🌻 Service info
route.get('/', (ctx) => {
  const { store } = ctx as Context
  return Home.getInfo(store.service)
}, {
  response: HomeModel.response,
})

// 🌻 Privacy policy
route.get('/privacy/', () => {
  return new Response(Bun.file(`${import.meta.dir}/get_privacy.html`), {
    headers: { 'Content-Type': 'text/html; charset=utf-8' },
  })
}, {})

export default route
