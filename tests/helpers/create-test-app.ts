import { Elysia } from 'elysia'
import type { Logger } from 'logixlysia'
import Service from '@/classes/Service'

export type ElysiaService = Elysia & {
  store: {
    beforeTime?: bigint
    service: Service
    logger: Logger
  }
}

export function onRequest({ request, set, store }: any)
{
  // console.log('onRequest()')
}

export function onResponse({ set, store }: any)
{
  // console.log('onResponse()')
}

export function onError({ request, set, store, error }: any)
{
  // console.error('onError()')
}

export async function createTestApp(): Promise<ElysiaService>
{
  const app = new Elysia()
  const service = new Service()
  await service.setup()
  app.state('service', service)
  app.onBeforeHandle({ as: 'global' }, onRequest)
  app.onAfterHandle({ as: 'global' }, onResponse)
  app.onError(onError)
  return app as ElysiaService
}
