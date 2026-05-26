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
app.use(routes.app)
app.use(routes.article)
app.use(routes.auth)
app.use(routes.options)

// set listen server
app.listen({
  port: server.port,
  hostname: server.host,
  development: service.dev,
})

// print server message
openServer(app)
