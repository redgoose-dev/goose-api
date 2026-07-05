import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_MOD, PATTERN_TAG, PATTERN_DATE } from '@/libs/validation'

export const ArticleModel = {

  getIndexQuery: t.Object({
    app: t.Optional(t.Numeric()),
    nest: t.Optional(t.Numeric()),
    category: t.Optional(t.Numeric()),
    q: t.Optional(t.String()),
    mode: t.Optional(t.String()),
    duration: t.Optional(t.String()),
    random: t.Optional(t.String()),
    field: t.Optional(t.String()),
    page: t.Optional(t.Numeric({ minimum: 0 })),
    size: t.Optional(t.Numeric({ minimum: 1 })),
    order: t.Optional(t.String()),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  getItemQuery: t.Object({
    field: t.Optional(t.String()),
    app: t.Optional(t.Numeric()),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  patchItemBody: t.Object({
    app: t.Optional(t.Numeric()),
    nest: t.Optional(t.Numeric()),
    category: t.Optional(t.Numeric()),
    title: t.Optional(t.String()),
    content: t.Optional(t.String()),
    json: t.Optional(t.Union([
      t.String(),
      t.Object({}, { additionalProperties: true }),
    ])),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
    mode: t.Optional(t.String()),
    regdate: t.Optional(t.String({ pattern: PATTERN_DATE })),
  }),

  patchChangeNestBody: t.Object({
    nest: t.Optional(t.Numeric()),
  }),

  patchUpBody: t.Object({
    mode: t.Optional(t.Union([
      t.Literal('hit'),
      t.Literal('star'),
    ])),
  }),

} as const

export type ArticleModel = {
  [k in keyof typeof ArticleModel]: UnwrapSchema<typeof ArticleModel[k]>
}
