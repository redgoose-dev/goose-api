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

export async function createTestApp(): Promise<ElysiaService>
{
  const app = new Elysia()
  const service = new Service()
  await service.setup()
  app.state('service', service)
  return app as ElysiaService
}
