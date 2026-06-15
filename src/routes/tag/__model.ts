import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_TAG } from '@/libs/validation'
import { MODULE } from './__helper'

export const TagModel = {

  getIndexQuery: t.Object({
    module: t.Optional(
      t.Union([
        t.Literal(MODULE.ARTICLE),
        t.Literal(MODULE.CHECKLIST),
        t.Literal(MODULE.JSON),
      ])
    ),
    module_srl: t.Optional(t.Numeric()),
    name: t.Optional(t.String()),
    page: t.Optional(t.Numeric({ minimum: 0 })),
    size: t.Optional(t.Numeric({ minimum: 1 })),
    order: t.Optional(t.String()),
    sort: t.Optional(t.UnionEnum([ 'desc', 'asc' ])),
  }),

  patchItemBody: t.Object({
    module: t.Optional(t.UnionEnum([ MODULE.ARTICLE, MODULE.CHECKLIST, MODULE.JSON ])),
    module_srl: t.Optional(t.Numeric()),
    tags: t.Optional(t.String({ pattern: PATTERN_TAG })),
  }),

  deleteItemBody: t.Object({
    module: t.UnionEnum([ MODULE.ARTICLE, MODULE.CHECKLIST, MODULE.JSON ]),
    module_srl: t.Numeric(),
  }),

} as const

export type TagModel = {
  [k in keyof typeof TagModel]: UnwrapSchema<typeof TagModel[k]>
}
