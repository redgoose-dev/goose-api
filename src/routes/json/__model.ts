import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_FIELD, PATTERN_MOD, PATTERN_TAG } from '@/libs/validation'

export const JsonModel = {

  getIndexQuery: t.Object({
    category: t.Optional(t.Numeric()),
    name: t.Optional(t.String()),
    field: t.Optional(t.String({ pattern: PATTERN_FIELD })),
    page: t.Optional(t.Numeric({ minimum: 0 })),
    size: t.Optional(t.Numeric({ minimum: 1 })),
    order: t.Optional(t.String()),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  getItemQuery: t.Object({
    field: t.Optional(t.String({ pattern: PATTERN_FIELD })),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  putItemBody: t.Object({
    category: t.Optional(t.Numeric()),
    name: t.String(),
    description: t.Optional(t.String()),
    json: t.String(),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
  }),

  patchItemBody: t.Object({
    category: t.Optional(t.Numeric()),
    name: t.Optional(t.String()),
    description: t.Optional(t.String()),
    json: t.Optional(t.String()),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
  }),

} as const

export type JsonModel = {
  [k in keyof typeof JsonModel]: UnwrapSchema<typeof JsonModel[k]>
}
