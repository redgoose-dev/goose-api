import { Elysia } from 'elysia'
import Service from '@/classes/Service'

export async function createTestApp()
{
  const app = new Elysia()
  const service = new Service()
  await service.setup()
  app.state('service', service)
  return app
}
