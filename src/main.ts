import { Elysia } from 'elysia'
import Service from '@/classes/Service'
import { openServer } from '@/libs/server'
import { onRequest, onResponse, onErrorBefore, onErrorAfter } from '@/libs/service'
import logging from '@/libs/logging'
import * as routes from '@/routes'

const { HOST, PORT } = Bun.env

// setup service
const service = new Service()
await service.setup()

// set server
const server = {
  host: HOST,
  port: Number(PORT),
  dev: service.dev,
}

// set server instance
const app = new Elysia()

// set state
app.state('service', service)

// set hooks
app.onError(onErrorBefore)

// setup logging
app.use(logging)

// set hooks
app.onBeforeHandle({ as: 'global' }, onRequest)
app.onAfterHandle({ as: 'global' }, onResponse)
app.onError(onErrorAfter)

// set routes
app.use(routes.home)
app.use(routes.auth)
app.use(routes.app)
app.use(routes.nest)
app.use(routes.category)
app.use(routes.article)
app.use(routes.comment)
app.use(routes.checklist)
app.use(routes.file)
app.use(routes.json)
app.use(routes.tag)
app.use(routes.preference)
app.use(routes.options)

// set listen server
app.listen({
  port: server.port,
  hostname: server.host,
  development: service.dev,
})

// print server message
openServer(app)

// DEV: 검사용
// if (server.dev)
// {
//   setInterval(() => {
//     console.log(`[${Date.now()}]`, (app.store as any).service.data.oAuth.keys())
//   }, 2000)
// }
