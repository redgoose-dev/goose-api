import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_FIELD, PATTERN_MIME } from '@/libs/validation'
import { BaseModel } from '@/libs/models'
import { RESIZE_TYPE } from '@/libs/assets'
import { MODULE } from './__helper'

export const FileModel = {

  getIndexQuery: t.Object({
    module: t.Optional(t.Union([
      t.Literal(MODULE.ARTICLE),
      t.Literal(MODULE.CHECKLIST),
      t.Literal(MODULE.COMMENT),
      t.Literal(MODULE.JSON),
    ])),
    module_srl: t.Optional(t.Numeric()),
    name: t.Optional(t.String()),
    mime: t.Optional(t.String()),
    field: t.Optional(t.String({ pattern: PATTERN_FIELD })),
    page: t.Optional(t.Numeric({ minimum: 0 })),
    size: t.Optional(t.Numeric({ minimum: 1 })),
    order: t.Optional(t.String()),
    sort: t.Optional(t.UnionEnum([ 'desc', 'asc' ])),
  }),

  getItemQuery: t.Object({
    ...BaseModel.modelAuthQuery.properties,
    w: t.Optional(t.Numeric()),
    h: t.Optional(t.Numeric()),
    t: t.Optional(t.Union([
      t.Literal(RESIZE_TYPE.COVER),
      t.Literal(RESIZE_TYPE.CONTAIN),
      t.Literal(RESIZE_TYPE.FILL),
      t.Literal(RESIZE_TYPE.INSIDE),
      t.Literal(RESIZE_TYPE.OUTSIDE),
    ])),
    q: t.Optional(t.Numeric({
      minimum: 1,
      maximum: 100,
    })),
  }),

  putItemBody: t.Object({
    module: t.Union([
      t.Literal(MODULE.ARTICLE),
      t.Literal(MODULE.JSON),
      t.Literal(MODULE.CHECKLIST),
      t.Literal(MODULE.COMMENT),
    ]),
    module_srl: t.Numeric(),
    file: t.File(),
    dir_name: t.Optional(t.String({
      default: 'origin',
    })),
    json: t.Optional(t.Union([
      t.String(),
      t.Object({}, { additionalProperties: true }),
    ])),
    format: t.Optional(t.String({
      pattern: PATTERN_MIME,
    })),
    quality: t.Optional(t.Numeric({
      minimum: 1,
      maximum: 100,
    })),
  }),

  patchItemBody: t.Object({
    dir_name: t.Optional(t.String({
      default: 'origin',
    })),
    file: t.Optional(t.File()),
    json: t.Optional(t.Union([
      t.String(),
      t.Object({}, { additionalProperties: true }),
    ])),
    format: t.Optional(t.String({
      pattern: PATTERN_MIME,
    })),
    quality: t.Numeric({
      default: 95,
      minimum: 1,
      maximum: 100,
    }),
  }),

} as const

export type FileModel = {
  [k in keyof typeof FileModel]: UnwrapSchema<typeof FileModel[k]>
}
