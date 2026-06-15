import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { BaseModel } from '@/libs/models'

export const PreferenceModel = {

  patchItemBody: t.Object({
    json: t.String(),
    change: t.Optional(BaseModel.booleanLike),
  }),

}

export type PreferenceModel = {
  [k in keyof typeof PreferenceModel]: UnwrapSchema<typeof PreferenceModel[k]>
}
