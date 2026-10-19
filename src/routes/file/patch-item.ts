import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { createCode } from '@/libs/strings'
import { deleteFile, getUploadPath } from '@/libs/file'
import { parseJSON, isObject, filteringObject } from '@/libs/objects'
import * as messages from '@/libs/messages'
import * as helper from './__helper'
import type Service from '@/classes/Service'
import type { FileModel } from './__model'

type PatchItemParams = {
  srl: number
  body: FileModel['patchItemBody']
  service: Service
}

export default async function patchItem({ srl, body, service }: PatchItemParams)
{
  let _path: string = ''
  try
  {
    // check item
    const item = db.getData({
      table: DB.TABLE.FILE,
      where: `srl = ${srl}`,
    })
    if (!item.data)
    {
      throw new ServiceError('No data', { status: 204 })
    }

    // set ready update
    let _ready: ZZ = {
      json: undefined,
      file: undefined,
    }

    // update json
    if (isObject(body.json))
    {
      const _json = parseJSON(item.data.json) || {}
      _ready.json = {
        ...(_json.width && { width: _json.width }),
        ...(_json.height && { height: _json.height }),
        ...body.json,
      }
    }

    // change file
    if (body.file)
    {
      // set json
      if (!_ready.json)
      {
        _ready.json = { ...(parseJSON(item.data.json) || {}) }
      }
      // check file size
      if (body.file.size > service.preference['file.limit.size'])
      {
        throw new ServiceError('File size limit exceeded.', { status: 400 })
      }
      // convert file to resource
      const _resource = await helper.fileToResource(body.file, body.format, body.quality)
      _path = await getUploadPath(body.dir_name, `${createCode(12)}.${_resource.ext}`)
      await Bun.write(_path, _resource.bytes)
      // 파일 교체 시 이전 이미지의 크기가 남지 않도록 한다.
      delete _ready.json.width
      delete _ready.json.height
      if (_resource.width !== undefined)
      {
        _ready.json.width = _resource.width
        _ready.json.height = _resource.height
      }
      _ready.file = _resource
    }

    // check update data
    _ready = filteringObject(_ready)
    if (Object.keys(_ready).length <= 0)
    {
      throw new ServiceError(messages.ERR_CANT_UPDATE, { status: 400 })
    }

    // update data
    db.editData({
      table: DB.TABLE.FILE,
      where: `srl = ${srl}`,
      set: [
        _ready.json !== undefined && 'json = $json',
        ...(_ready.file ? [
          'name = $name',
          'path = $path',
          'mime = $mime',
          'size = $size',
        ] : []),
      ].filter(Boolean),
      values: {
        '$json': JSON.stringify(_ready.json),
        ...(_ready.file ? {
          '$name': _ready.file.name,
          '$path': _path,
          '$mime': _ready.file.mime,
          '$size': _ready.file.size,
        } : {}),
      },
    })

    // get item
    const updatedItem = db.getData({
      table: DB.TABLE.FILE,
      where: `srl = ${srl}`,
    })

    // delete legacy file
    if (_ready.file && item.data.path) await deleteFile(item.data.path)

    // delete cache
    if (_ready.file) await helper.deleteCache(item.data.code)

    return {
      ...updatedItem.data,
      path: undefined,
      json: parseJSON(updatedItem.data.json),
    }
  }
  catch (_e: any)
  {
    // delete error file
    if (_path) await deleteFile(_path)
    throw new ServiceError('Failed to update File.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
