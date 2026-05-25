import { t } from 'elysia'

export const PATTERN_FIELD = '^[A-Za-z0-9_]+(?:,[A-Za-z0-9_]+)*$'
export const PATTERN_MOD = '^[A-Za-z0-9_-]+(?:,[A-Za-z0-9_-]+)*$'

export const BooleanLike = t.Transform(t.Union([
  t.Boolean(),
  t.BooleanString(),
  t.Numeric({ minimum: 0, maximum: 1 }),
  t.Literal('0'),
  t.Literal('1'),
]))
  .Decode((value) => Boolean(value))
  .Encode((value) => value)

