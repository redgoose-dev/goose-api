import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
// import { checkingToken } from '@/libs/verify'
// import { getSharp } from '@/libs/external'
import type Service from '@/classes/Service'
import { createCode } from '@/libs/strings'
import { parseJSON } from '@/libs/objects'
import { getUploadPath, deleteFile } from '@/libs/file'
import type { BaseModel } from '@/libs/models'
import type { FileModel } from './model'

type PutItemParams = {
  body: FileModel['putItemBody']
  service: Service
}
type FileToResource = {
  name: string
  mime: string
  ext: string
  size: number
  bytes?: Uint8Array
  image?: Bun.Image
}

export const MODULE = {
  ARTICLE: 'article',
  CHECKLIST: 'checklist',
  COMMENT: 'comment',
  JSON: 'json',
}

export abstract class FileTool {
  static getOriginTableName(module: string): string
  {
    switch (module)
    {
      case MODULE.ARTICLE:
        return DB.TABLE.ARTICLE
      case MODULE.CHECKLIST:
        return DB.TABLE.CHECKLIST
      case MODULE.COMMENT:
        return DB.TABLE.COMMENT
      case MODULE.JSON:
        return DB.TABLE.JSON
      default:
        return ''
    }
  }
  /**
   * 이미지를 다른 이미지 포맷과 퀄리티로 변환한다.
   */
  static convertImageFormat(image: Bun.Image, format?: string, quality?: number)
  {
    if (format === undefined && quality === undefined) return image
    const _quality = quality === undefined ? 95 : quality
    switch (format)
    {
      case 'image/png':
        const compressionLevel = Math.round((1 - _quality / 100) * 9)
        return image.png({ compressionLevel })
      case 'image/jpeg':
        return image.jpeg({ quality: _quality })
      case 'image/webp':
        return image.webp({ quality: _quality })
      case 'image/avif':
        return image.avif({ quality: _quality })
      default:
        return image
    }
  }
  /**
   * File 객체를 사용할 수 있도록 증류한다.
   */
  static async fileToResource(file: File): Promise<FileToResource>
  {
    const bytes = await file.bytes()
    return {
      name: file.name,
      mime: file.type,
      ext: (file.name.split('.').pop() ?? file.type.split('/')[1]) as string,
      size: file.size,
      ...(/^image/.test(file.type) ? {
        image: new Bun.Image(bytes),
      } : {
        bytes,
      })
    }
  }
  static count(op: BaseModel['paramsTableSelect']): number
  {
    const count = db.getCount({
      table: DB.TABLE.FILE,
      where: op.where || '',
      values: op.values || {},
    })
    return count.data || 0
  }
}

export abstract class File_ {

  static async getItem()
  {
    // TODO: example sharp
    // const sharp = await getSharp()
    // const _foo = await sharp(_buffer)
    //   .resize({ width: 100, height: 50 })
    //   .webp()
    //   .toFile('FOO.webp')
    try
    {}
    catch (_e: any)
    {}
  }

  static async putItem({ body, service }: PutItemParams)
  {
    let _resource: FileToResource
    let _path: string = ''
    try
    {
      // check exist origin data
      const originCount = db.getCount({
        table: FileTool.getOriginTableName(body.module),
        where: `srl = ${body.module_srl}`,
      })
      if (originCount.data <= 0)
      {
        throw new ServiceError('Not found origin data.', { status: 400 })
      }
      // check file size
      if (body.file.size > service.preference['file.limitSize'])
      {
        throw new ServiceError('File size limit exceeded.', { status: 400 })
      }
      // convert file to resource
      _resource = await FileTool.fileToResource(body.file)
      // convert format or quality
      if (_resource.image)
      {
        _resource.image = FileTool.convertImageFormat(_resource.image, _resource.mime, body.quality)
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

}
