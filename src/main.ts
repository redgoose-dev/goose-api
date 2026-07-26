import { Elysia } from 'elysia'
import Service from '@/classes/Service'
import { openServer } from '@/libs/server'
import { onResponse, onErrorAfter } from '@/libs/service'
import logging, { closeLogging } from '@/libs/logging'
import { LOG_IGNORE_PATHS_KEY, setLogIgnoredPaths } from '@/libs/logging/ignore'
import { clearRequestTracking, registerRequestTracking } from '@/libs/logging/request'
import * as routes from '@/routes'

const { HOST, PORT } = Bun.env

// setup service
const service = new Service()
await service.setup()
setLogIgnoredPaths(service.preference[LOG_IGNORE_PATHS_KEY])

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

// setup logging
app.use(logging)
app.onRequest(({ request, store, server }: any) => {
  registerRequestTracking(request, store.logger.getContext(request), server)
})
app.onAfterResponse({ as: 'global' }, ({ request }) => {
  clearRequestTracking(request)
})
app.onError(({ request }) => {
  clearRequestTracking(request)
})

// set hooks
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
app.use(routes.log)
app.use(routes.mix)
app.use(routes.options)
app.onStop(closeLogging)

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
