import { t } from 'elysia'
import type { UnwrapSchema } from 'elysia'
import { PATTERN_FIELD, PATTERN_MIME } from '@/libs/validation'
import { MODULE } from './service'

export const FileModel = {

  putItemBody: t.Object({
    module: t.Union([
      t.Literal(MODULE.ARTICLE),
      t.Literal(MODULE.JSON),
      t.Literal(MODULE.CHECKLIST),
      t.Literal(MODULE.COMMENT),
    ]),
    module_srl: t.Numeric(),
    dir_name: t.String({ default: 'origin' }),
    file: t.File(),
    json: t.Union([
      t.String(),
      t.Object({}, { additionalProperties: true }),
    ], {
      default: {},
    }),
    format: t.String({
      pattern: PATTERN_MIME,
      default: '',
    }),
    quality: t.Numeric({
      default: 95,
      minimum: 1,
      maximum: 100,
    }),
  }),

  patchItemBody: t.Object({
    // TODO
  }),

}

export type FileModel = {
  [k in keyof typeof FileModel]: UnwrapSchema<typeof FileModel[k]>
}
