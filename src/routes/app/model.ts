import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_FIELD, PATTERN_MOD } from '@/libs/validation'

export const AppModel = {

  getIndexQuery: t.Object({
    code: t.Optional(t.String()),
    name: t.Optional(t.String()),
    field: t.Optional(t.String({ pattern: PATTERN_FIELD })),
    page: t.Optional(t.Numeric({ minimum: 0 })),
    size: t.Optional(t.Numeric({ minimum: 1 })),
    order: t.Optional(t.String()),
    sort: t.Optional(t.UnionEnum([ 'desc', 'asc' ])),
    mod: t.Optional(t.String({ pattern: PATTERN_MOD })),
  }),

  getItemParams: t.Object({
    srl: t.Union([ t.Number(), t.String() ]),
  }),
  getItemQuery: t.Object({
    field: t.Optional(t.String()),
    mod: t.Optional(t.String()),
  }),

  putItemBody: t.Object({
    code: t.String(),
    name: t.String(),
    description: t.Optional(t.String()),
  }),

  patchItemBody: t.Object({
    code: t.Optional(t.String()),
    name: t.Optional(t.String()),
    description: t.Optional(t.String()),
  }),

} as const

export type AppModel = {
  [k in keyof typeof AppModel]: UnwrapSchema<typeof AppModel[k]>
}
