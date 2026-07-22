import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { MODULE } from './__helper'

export const CommentModel = {

  getIndexQuery: t.Object({
    module: t.Optional(t.Union([
      t.Literal(MODULE.ARTICLE),
    ])),
    module_srl: t.Optional(t.Numeric()),
    q: t.Optional(t.String()),
    field: t.Optional(t.String()),
    page: t.Optional(t.Numeric({ minimum: 0 })),
    size: t.Optional(t.Numeric({ minimum: 1 })),
    order: t.Optional(t.String()),
  }),

  getItemQuery: t.Object({
    field: t.Optional(t.String()),
  }),

  putItemBody: t.Object({
    module: t.Union([
      t.Literal(MODULE.ARTICLE),
    ]),
    module_srl: t.Numeric(),
    content: t.String(),
  }),

  patchItemBody: t.Object({
    module: t.Optional(t.Union([
      t.Literal(MODULE.ARTICLE),
    ])),
    module_srl: t.Optional(t.Numeric()),
    content: t.Optional(t.String()),
  }),

} as const

export type CommentModel = {
  [k in keyof typeof CommentModel]: UnwrapSchema<typeof CommentModel[k]>
}
