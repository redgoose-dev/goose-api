import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import { LOG_IGNORE_PATHS_KEY, setLogIgnoredPaths } from '@/libs/logging/ignore'
import { parseJSON } from '@/libs/objects'
import type { PreferenceModel } from './__model'

type PatchItemParams = {
  body: PreferenceModel['patchItemBody'],
  service: Service,
}

export default async function patchItem({ body, service }: PatchItemParams)
{
  try
  {
    const _json = parseJSON(body.json)
    await service.updatePreference(_json, body.change as boolean)
    setLogIgnoredPaths(service.preference[LOG_IGNORE_PATHS_KEY])
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to edit Preference.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
