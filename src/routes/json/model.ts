import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_TAG } from '@/libs/validation'

export const JsonModel = {

  putItemBody: t.Object({
    category: t.Optional(t.Numeric()),
    name: t.String(),
    description: t.Optional(t.String()),
    json: t.String(),
    tag: t.Optional(t.String({ pattern: PATTERN_TAG })),
  }),

}

export type JsonModel = {
  [k in keyof typeof JsonModel]: UnwrapSchema<typeof JsonModel[k]>
}
