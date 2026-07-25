import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_DATE } from '@/libs/validation'

const PATTERN_LOG_LEVEL = '^(DEBUG|INFO|WARNING|ERROR)(,(DEBUG|INFO|WARNING|ERROR))*$'

export const LogModel = {

  getIndexQuery: t.Object({
    cursor: t.Optional(t.String({ minLength: 1, maxLength: 500 })),
    size: t.Optional(t.Numeric({ minimum: 1, maximum: 100 })),
    total: t.Optional(t.Numeric({ minimum: 0, maximum: 1 })),
    level: t.Optional(t.String({ pattern: PATTERN_LOG_LEVEL })),
    from: t.Optional(t.String({ pattern: PATTERN_DATE })),
    to: t.Optional(t.String({ pattern: PATTERN_DATE })),
    status: t.Optional(t.Numeric({ minimum: 100, maximum: 599 })),
    method: t.Optional(t.String({ minLength: 1, maxLength: 20 })),
    path: t.Optional(t.String({ minLength: 1, maxLength: 500 })),
    request_id: t.Optional(t.String({ minLength: 1, maxLength: 200 })),
    q: t.Optional(t.String({ minLength: 2, maxLength: 200 })),
  }),

  getItemParams: t.Object({
    id: t.Numeric({ minimum: 1 }),
  }),

  getSummaryQuery: t.Object({
    from: t.Optional(t.String({ pattern: PATTERN_DATE })),
    to: t.Optional(t.String({ pattern: PATTERN_DATE })),
    interval: t.Optional(t.Union([
      t.Literal('hour'),
      t.Literal('day'),
    ])),
  }),

} as const

export type LogModel = {
  [k in keyof typeof LogModel]: UnwrapSchema<typeof LogModel[k]>
}
