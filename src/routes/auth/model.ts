import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_CODE, ModelAuthQuery } from '@/libs/validation'

export const AuthModel = {

  postLoginBody: t.Object({
    id: t.String({ pattern: PATTERN_CODE }),
    password: t.String(),
  })

} as const

export type AuthModel = {
  [k in keyof typeof AuthModel]: UnwrapSchema<typeof AuthModel[k]>
}
