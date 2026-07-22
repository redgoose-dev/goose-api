import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'

export const HomeModel = {
  response: t.Object({
    message: t.String(),
    version: t.String(),
    dev: t.Boolean(),
  })
} as const

export type HomeModel = {
  [k in keyof typeof HomeModel]: UnwrapSchema<typeof HomeModel[k]>
}
