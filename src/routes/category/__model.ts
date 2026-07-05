import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_FIELD, PATTERN_MOD, PATTERN_TAG, PATTERN_SRLS } from '@/libs/validation'
import { MODULE } from './__helper'

export const CategoryModel = {

  getIndexQuery: t.Object({
    module: t.Optional(t.Union([
      t.Literal(MODULE.NEST),
      t.Literal(MODULE.JSON),
    ])),
    module_srl: t.Optional(t.Numeric()),
    name: t.Optional(t.String()),
    q: t.Optional(t.String()),
    field: t.Optional(t.String({ pattern: PATTERN_FIELD })),
    page: t.Optional(t.Numeric({ minimum: 0 })),
    size: t.Optional(t.Numeric({ minimum: 1 })),
    order: t.Optional(t.String()),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  getItemQuery: t.Object({
    field: t.Optional(t.String({ pattern: PATTERN_FIELD })),
  }),

  putItemBody: t.Object({
    module: t.Union([
      t.Literal(MODULE.NEST),
      t.Literal(MODULE.JSON),
    ]),
    module_srl: t.Optional(t.Numeric()),
    name: t.String(),
  }),

  patchItemBody: t.Object({
    name: t.Optional(t.String()),
  }),

  patchChangeOrderBody: t.Object({
    module: t.Union([
      t.Literal(MODULE.NEST),
      t.Literal(MODULE.JSON),
    ]),
    module_srl: t.Optional(t.Numeric()),
    srls: t.String({ pattern: PATTERN_SRLS })
  }),

} as const

export type CategoryModel = {
  [k in keyof typeof CategoryModel]: UnwrapSchema<typeof CategoryModel[k]>
}
