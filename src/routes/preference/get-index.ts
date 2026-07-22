import Service from '@/classes/Service'

export default async function getIndex(service: Service)
{
  return await service.loadPreference()
}
