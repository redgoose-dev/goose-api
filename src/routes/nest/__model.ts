import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_MOD, PATTERN_CODE } from '@/libs/validation'

export const NestModel = {

  getIndexQuery: t.Object({
    app: t.Optional(t.Numeric()),
    code: t.Optional(t.String({ pattern: PATTERN_CODE })),
    name: t.Optional(t.String()),
    field: t.Optional(t.String()),
    page: t.Optional(t.Numeric({ minimum: 0 })),
    size: t.Optional(t.Numeric({ minimum: 1 })),
    order: t.Optional(t.String()),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  getItemQuery: t.Object({
    app: t.Optional(t.Numeric()),
    field: t.Optional(t.String()),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  putItemBody: t.Object({
    app: t.Numeric(),
    code: t.String({ pattern: PATTERN_CODE }),
    name: t.String(),
    description: t.Optional(t.String()),
    json: t.Union([
      t.String(),
      t.Object({}, { additionalProperties: true }),
    ], { default: {} }),
  }),

  patchItemBody: t.Object({
    app: t.Optional(t.Numeric()),
    code: t.Optional(t.String({ pattern: PATTERN_CODE })),
    name: t.Optional(t.String()),
    description: t.Optional(t.String()),
    json: t.Optional(t.Union([
      t.String(),
      t.Object({}, { additionalProperties: true }),
    ])),
  }),

} as const

export type NestModel = {
  [k in keyof typeof NestModel]: UnwrapSchema<typeof NestModel[k]>
}
