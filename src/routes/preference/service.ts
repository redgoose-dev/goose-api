import ServiceError from '@/classes/ServiceError'
import Service from '@/classes/Service'
import { parseJSON } from '@/libs/objects'
import type { PreferenceModel } from './model'

type PatchItemBody = {
  body: PreferenceModel['patchItemBody'],
  service: Service,
}

export abstract class Preference {

  static async getItem(service: Service)
  {
    return await service.loadPreference()
  }

  static async patchItem({ body, service }: PatchItemBody)
  {
    try
    {
      const _json = parseJSON(body.json)
      await service.updatePreference(_json, body.change as boolean)
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

}
