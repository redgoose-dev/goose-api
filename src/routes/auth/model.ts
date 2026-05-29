import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_CODE, PATTERN_URL, PATTERN_EMAIL, ModelAuthQuery } from '@/libs/validation'

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
  }),

  putProviderBody: t.Object({
    id: t.String({ pattern: PATTERN_CODE }),
    name: t.String(),
    avatar: t.Optional(t.String({ pattern: PATTERN_URL })),
    email: t.Optional(t.String({ pattern: PATTERN_EMAIL })),
    password: t.String(),
  }),

  patchProviderParams: t.Object({
    srl: t.Number(),
  }),
  patchProviderBody: t.Object({
    id: t.Optional(t.String({ pattern: PATTERN_CODE })),
    name: t.Optional(t.String()),
    avatar: t.Optional(t.String({ pattern: PATTERN_URL })),
    email: t.Optional(t.String({ pattern: PATTERN_EMAIL })),
    password: t.Optional(t.String()),
  }),

  deleteProviderParams: t.Object({
    srl: t.Number(),
  }),

} as const

export type AuthModel = {
  [k in keyof typeof AuthModel]: UnwrapSchema<typeof AuthModel[k]>
}
