import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import * as messages from '@/libs/messages'
import { parseJSON, filteringObject } from '@/libs/objects'
import * as categoryHelper from '@/routes/category/__helper'
import * as tagHelper from '@/routes/tag/__helper'
import * as fileHelper from '@/routes/file/__helper'
import * as helper from './__helper'
import type { ArticleModel } from './__model'

type PatchItemParams = {
  srl: number
  body: ArticleModel['patchItemBody']
}

export default async function patchItem({ srl, body }: PatchItemParams)
{
  let _transaction = false
  try
  {
    // get item
    const article = db.getData({
      table: DB.TABLE.ARTICLE,
      field: 'srl,mode,regdate',
      where: `srl = ${srl}`,
    })
    if (!article.data) throw new ServiceError('No data', { status: 204 })

    // set ready update
    let _ready: ZZ = {
      nest_srl: undefined,
      category_srl: undefined,
      title: undefined,
      content: undefined,
      json: undefined,
      mode: undefined,
      regdate: undefined,
    }

    // check nest
    if (body.nest)
    {
      const _count = db.getCount({
        table: DB.TABLE.NEST,
        where: `srl = ${body.nest}`,
      })
      if (_count.data <= 0) throw new ServiceError('Not found Nest.', { status: 400 })
      _ready.nest_srl = body.nest
    }

    // check category
    if ((body.category || 0) > 0)
    {
      const _count = db.getCount({
        table: DB.TABLE.CATEGORY,
        where: `srl = ${body.category} AND module LIKE \'${categoryHelper.MODULE.NEST}\'`,
      })
      if (_count.data <= 0) throw new ServiceError('Not found Category.', {status: 400})
    }
    if (body.category !== undefined)
    {
      _ready.category_srl = body.category > 0 ? body.category : 0
    }

    // set title
    if (body.title)
    {
      _ready.title = body.title.trim().replace(/\s+/g, ' ')
    }

    // set content
    if (body.content)
    {
      _ready.content = body.content
    }

    // set json
    if (body.json)
    {
      _ready.json = parseJSON(body.json)
      if (!_ready.json) throw new ServiceError('Invalid json data.', { status: 400 })
    }

    // set mode
    if (body.mode)
    {
      if (!helper.STATUS.check(body.mode))
      {
        throw new ServiceError('Invalid mode.', { status: 400 })
      }
      _ready.mode = body.mode
    }
    else if (article.data.mode === helper.STATUS.READY)
    {
      _ready.mode = helper.STATUS.PUBLIC
    }

    // set regdate
    if (body.regdate)
    {
      _ready.regdate = body.regdate
    }

    // check update data
    _ready = filteringObject(_ready)
    if (!(Object.keys(_ready).length > 0 || body.tag))
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }

    // begin transaction
    _transaction = db.transaction('begin')

    // update data
    db.editData({
      table: DB.TABLE.ARTICLE,
      where: `srl = ${srl}`,
      set: [
        _ready.nest_srl !== undefined && 'nest_srl = $nest_srl',
        _ready.category_srl !== undefined && `category_srl = ${_ready.category_srl === 0 ? 'NULL' : '$category_srl'}`,
        _ready.title !== undefined && 'title = $title',
        _ready.content !== undefined && 'content = $content',
        _ready.json !== undefined && 'json = $json',
        _ready.mode !== undefined && 'mode = $mode',
        _ready.regdate !== undefined && 'regdate = $regdate',
        article.data.mode === helper.STATUS.READY && `created_at = ${DB.DATE_TIME}`,
        `updated_at = ${DB.DATE_TIME}`,
      ],
      values: {
        '$nest_srl': _ready.nest_srl,
        '$category_srl': _ready.category_srl || 0,
        '$title': _ready.title,
        '$content': _ready.content,
        '$json': _ready.json ? JSON.stringify(_ready.json) : undefined,
        '$mode': _ready.mode,
        '$regdate': _ready.regdate,
      },
    })

    // update tag
    if (body.tag)
    {
      tagHelper.update({
        module: tagHelper.MODULE.ARTICLE,
        module_srl: srl,
        tags: body.tag.split(',') || [],
      })
    }

    // clear file cache
    if (article.data.mode !== _ready.mode)
    {
      const files = db.getIndex({
        table: DB.TABLE.FILE,
        field: 'code',
        where: [
          `AND module LIKE \'${fileHelper.MODULE.ARTICLE}\'`,
          `AND module_srl = ${srl}`,
        ],
      })
      for await (const file of files.data)
      {
        await fileHelper.deleteCache(file.code)
      }
    }

    // commit transaction
    _transaction = db.transaction('commit')
  }
  catch (_e: any)
  {
    // collback transaction
    db.transaction('rollback', _transaction)

    throw new ServiceError('Failed to edit Article.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
