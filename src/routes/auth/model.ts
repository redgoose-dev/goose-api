import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_CODE, PATTERN_URL, PATTERN_EMAIL, PATTERN_MOD } from '@/libs/validation'

export const AuthModel = {

  getRedirectQuery: t.Object({
    redirect_uri: t.String(),
    access_token: t.Optional(t.String()),
  }),

  getCallbackQuery: t.Object({
    code: t.Optional(t.String()),
    error: t.Optional(t.String()),
    error_description: t.Optional(t.String()),
    state: t.Optional(t.String()),
  }),

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

  getProviderIndexQuery: t.Object({
    redirect_uri: t.String({ pattern: PATTERN_URL }),
  }),
  putProviderBody: t.Object({
    id: t.String({ pattern: PATTERN_CODE }),
    name: t.String(),
    avatar: t.Optional(t.String({ pattern: PATTERN_URL })),
    email: t.Optional(t.String({ pattern: PATTERN_EMAIL })),
    password: t.String(),
  }),
  patchProviderBody: t.Object({
    id: t.Optional(t.String({ pattern: PATTERN_CODE })),
    name: t.Optional(t.String()),
    avatar: t.Optional(t.String({ pattern: PATTERN_URL })),
    email: t.Optional(t.String({ pattern: PATTERN_EMAIL })),
    password: t.Optional(t.String()),
  }),

  getTokenQuery: t.Object({
    order: t.Optional(t.String()),
    sort: t.Optional(t.UnionEnum([ 'desc', 'asc' ])),
    token: t.Optional(t.String()),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),
  putTokenBody: t.Object({
    description: t.String(),
  }),
  patchTokenBody: t.Object({
    description: t.String(),
  }),

} as const

export type AuthModel = {
  [k in keyof typeof AuthModel]: UnwrapSchema<typeof AuthModel[k]>
}
