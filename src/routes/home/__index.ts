import { Elysia } from 'elysia'
import { HomeModel } from './__model'

const route = new Elysia()

// 🌻 Service info
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  const { store } = ctx as Context
  return await getIndex({
    service: store.service,
  })
}, {
  response: HomeModel.response,
})

// 🌻 Privacy policy
route.get('/privacy/', () => {
  return new Response(Bun.file(`${import.meta.dir}/get-privacy.html`), {
    headers: { 'Content-Type': 'text/html; charset=utf-8' },
  })
}, {})

export default route
