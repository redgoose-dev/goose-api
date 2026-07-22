import Service from '@/classes/Service'

type GetIndexParams = {
  service: Service
}

export default async function getIndex({ service }: GetIndexParams)
{
  return {
    message: `Hello! ${service.serviceName}`,
    version: service.version,
    dev: service.dev,
  }
}
