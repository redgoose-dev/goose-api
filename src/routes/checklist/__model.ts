import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_TAG, PATTERN_DATE, PATTERN_FIELD, PATTERN_MOD } from '@/libs/validation'

export const ChecklistModel = {

  getIndexQuery: t.Object({
    content: t.Optional(t.String()),
    start: t.Optional(t.String({ pattern: PATTERN_DATE })),
    end: t.Optional(t.String({ pattern: PATTERN_DATE })),
    field: t.Optional(t.String({ pattern: PATTERN_FIELD })),
    page: t.Optional(t.Numeric({ minimum: 0 })),
    size: t.Optional(t.Numeric({ minimum: 1 })),
    order: t.Optional(t.String()),
    sort: t.Optional(t.UnionEnum([ 'desc', 'asc' ])),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  getItemQuery: t.Object({
    field: t.Optional(t.String({ pattern: PATTERN_FIELD })),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  putItemBody: t.Object({
    content: t.Optional(t.String()),
    regdate: t.Optional(t.String()),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
  }),

  patchItemBody: t.Object({
    content: t.Optional(t.String()),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
  }),

} as const

export type ChecklistModel = {
  [k in keyof typeof ChecklistModel]: UnwrapSchema<typeof ChecklistModel[k]>
}
