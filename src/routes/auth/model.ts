import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_CODE, PATTERN_URL, ModelAuthQuery } from '@/libs/validation'

export const AuthModel = {

  postRenewBody: t.Object({
    refresh: t.String(),
  }),

  postReadyLogin: t.Object({
    redirect_uri: t.String({ pattern: PATTERN_URL }),
  }),

  postLoginBody: t.Object({
    id: t.String({ pattern: PATTERN_CODE }),
    password: t.String(),
  })

} as const

export type AuthModel = {
  [k in keyof typeof AuthModel]: UnwrapSchema<typeof AuthModel[k]>
}
