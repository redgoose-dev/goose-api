import { t } from 'elysia'

export const PATTERN_FIELD = '^[A-Za-z0-9_]+(?:,[A-Za-z0-9_]+)*$'
export const PATTERN_MOD = '^[A-Za-z0-9_-]+(?:,[A-Za-z0-9_-]+)*$'
export const PATTERN_CODE = '^[a-zA-Z0-9_-]+$'
export const PATTERN_URL = '^(https?:\\/\\/[^\\s]+|)$'
export const PATTERN_EMAIL = '^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$'

export const ModelAuthQuery = {
  '_a': t.Optional(t.String()),
}

export const BooleanLike = t.Transform(t.Union([
  t.Boolean(),
  t.BooleanString(),
  t.Numeric({ minimum: 0, maximum: 1 }),
  t.Literal('0'),
  t.Literal('1'),
]))
  .Decode((value) => Boolean(value))
  .Encode((value) => value)
