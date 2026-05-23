import { t } from 'elysia'

export const HomeModel = {
  response: t.Object({
    message: t.String(),
    version: t.String(),
    dev: t.Boolean(),
  })
} as const
