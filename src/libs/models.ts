import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PROVIDER_CODE, type ProviderCode } from '@/classes/provider'

/**
 * # `_a` 파라메터 사용하기
 * `t.Object({ ...BaseModel.modelAuthQuery.properties })` 로 객체 붙이기
 */

export const BaseModel = {

  modelAuthQuery: t.Object({
    '_a': t.Optional(t.String()),
  }),

  paramsSrl: t.Object({
    srl: t.Number(),
  }),
  paramsSrlCode: t.Object({
    srl: t.Union([ t.Number(), t.String() ]),
  }),

  booleanLike: t.Transform(t.Union([
    t.Boolean(),
    t.BooleanString(),
    t.Numeric({ minimum: 0, maximum: 1 }),
    t.Literal('0'),
    t.Literal('1'),
  ]))
    .Decode((value) => Boolean(value))
    .Encode((value) => value),

  paramsProvider: t.Object({
    provider: t.UnionEnum(Object.values(PROVIDER_CODE) as [ProviderCode, ...ProviderCode[]]),
  }),

} as const

export type BaseModel = {
  [k in keyof typeof BaseModel]: UnwrapSchema<typeof BaseModel[k]>
}
