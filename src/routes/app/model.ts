import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'

export const AppModel = {

  getItemParams: t.Object({
    srl: t.Number(),
  }),

  putItemBody: t.Object({
    code: t.String(),
    name: t.String(),
    description: t.Optional(t.String()),
  }),

} as const

export type AppModel = {
  [k in keyof typeof AppModel]: UnwrapSchema<typeof AppModel[k]>
}
