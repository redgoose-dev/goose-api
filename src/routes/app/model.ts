import { t } from 'elysia'

export const AppModel = {

  params: t.Object({
    srl: t.Number(),
  })

} as const
