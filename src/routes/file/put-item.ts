import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { createCode } from '@/libs/strings'
import { deleteFile, getUploadPath } from '@/libs/file'
import { parseJSON } from '@/libs/objects'
import * as helper from './__helper'
import type Service from '@/classes/Service'
import type { FileModel } from './__model'
import type { FileToResource } from './__helper'

type PutItemParams = {
  body: FileModel['putItemBody']
  service: Service
}

export default async function putItem({ body, service }: PutItemParams)
{
  let _path: string = ''
  try
  {
    // check exist origin data
    const originCount = db.getCount({
      table: helper.getModuleName(body.module),
      where: `srl = ${body.module_srl}`,
    })
    if (originCount.data <= 0)
    {
      throw new ServiceError('Not found origin data.', { status: 400 })
    }

    // check file size
    if (body.file.size > service.preference['file.limit.size'])
    {
      throw new ServiceError('File size limit exceeded.', { status: 400 })
    }

    // TODO: 등록할 수 있는 최대 갯수 제한검사

    // convert file to resource
    let _resource: FileToResource = await helper.fileToResource(body.file)

    // convert format or quality
    if (_resource.image)
    {
      _resource.image = helper.convertImageFormat(_resource.image, _resource.mime, body.quality)
    }

    // set save path
    _path = await getUploadPath(body.dir_name, `${createCode(12)}.${_resource.ext}`)

    // copy file
    if (_resource.image)
    {
      await _resource.image.write(_path)
    }
    else if (_resource.bytes)
    {
      await Bun.write(_path, _resource.bytes)
    }

    // set image size
    let _json: ZZ = body.json || {}
    if (_resource.image)
    {
      const _metadata = await _resource.image.metadata()
      _json.width = _metadata.width
      _json.height = _metadata.height
    }

    // add data
    const added = db.addData({
      table: DB.TABLE.FILE,
      values: [
        { key: 'code', value: createCode(8) },
        { key: 'name', value: _resource.name },
        { key: 'path', value: _path },
        { key: 'mime', value: _resource.mime },
        { key: 'size', value: _resource.size },
        { key: 'json', value: JSON.stringify(_json) },
        { key: 'module', value: body.module },
        { key: 'module_srl', value: body.module_srl },
        { key: 'created_at', valueName: DB.DATE_TIME },
      ],
    })

    // get item
    const item = db.getData({
      table: DB.TABLE.FILE,
      where: `srl = ${added.data}`,
    })

    return {
      ...item.data,
      path: undefined,
      json: parseJSON(item.data.json),
    }
  }
  catch (_e: any)
  {
    // delete error file
    if (_path) await deleteFile(_path)
    throw new ServiceError('Failed to add File.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}
