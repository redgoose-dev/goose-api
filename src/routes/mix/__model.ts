import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'

export const MixModel = {

  // postIndexBody: t.Array(
  //   t.Any()
  // ),
  postIndexBody: t.Array(
    t.Object({
      key: t.String(),
      method: t.Optional(t.String()),
      url: t.String(),
      if: t.Optional(t.String()),
      params: t.Optional(
        t.Record(t.String(), t.Any())
      ),
    })
  ),

} as const

export type MixModel = {
  [k in keyof typeof MixModel]: UnwrapSchema<typeof MixModel[k]>
}
