import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { checkingToken } from '@/libs/verify'
import type Service from '@/classes/Service'
import { Permission } from '@/libs/assets'
import { createCode } from '@/libs/strings'
import * as messages from '@/libs/messages'
import { parseJSON, isObject, filteringObject } from '@/libs/objects'
import { getUploadPath, deleteFile, existFile } from '@/libs/file'
import * as libs from './libs'
import type { BaseModel } from '@/libs/models'
import type { FileModel } from './model'

type GetIndexParams = {
  query: FileModel['getIndexQuery']
}
type GetItemParams = {
  code: string
  query: FileModel['getItemQuery']
  ctx: any
}
type PutItemParams = {
  body: FileModel['putItemBody']
  service: Service
}
type PatchItemParams = {
  srl: number
  body: FileModel['patchItemBody']
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
  static getModuleName(module: string): string
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
  static getModuleData(module: string, moduleSrl: number)
  {
    const _module = this.getModuleName(module)
    if (!_module) return null
    return db.getData({
      table: _module,
      where: `srl = ${moduleSrl}`,
    }).data
  }
}

export abstract class File_ {

  static async getIndex({ query }: GetIndexParams)
  {
    try
    {
      // set assets
      const _table = `${DB.TABLE.FILE} AS f`
      const _field = query.field ? query.field.split(',') : ''
      let _where: string[] = []
      let _values: ZZ = {}
      let _join: string[] = []
      if (query.module)
      {
        _where.push(`AND module LIKE $module`)
        _values['$module'] = query.module
      }
      if (query.module && query.module_srl)
      {
        _where.push(`AND module_srl = $module_srl`)
        _values['$module_srl'] = query.module_srl
      }
      if (query.name)
      {
        _where.push(`AND name LIKE '%' || $name || '%'`)
        _values['$name'] = query.name
      }
      if (query.mime)
      {
        _where.push(`AND mime LIKE '%' || $mime || '%'`)
        _values['$mime'] = query.mime
      }
      // get count
      const count = db.getCount({
        table: _table,
        where: _where,
        join: _join,
        values: _values,
      })
      if (count.data <= 0) throw new ServiceError('No data', { status: 204 })
      // get index
      const index = db.getIndex({
        table: _table,
        field: _field,
        where: _where,
        join: _join,
        order: Boolean(query.order || query.sort) ? query.order : 'srl',
        sort: Boolean(query.order || query.sort) ? query.sort : 'desc',
        page: query.page,
        size: query.size,
        values: _values,
      })
      const _index = index.data.map((o: ZZ) => {
        return {
          ...o,
          json: o.json ? parseJSON(o.json) : undefined,
          path: undefined,
        }
      })
      return {
        total: count.data,
        index: _index,
      }
    }
    catch (_e: any)
    {
      throw new ServiceError('Failed to get File index.', {
        status: _e.status,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async getItem({ code, query, ctx }: GetItemParams)
  {
    try
    {
      // set image options
      const _imageOption = libs.getImageOptions(query.w, query.h, query.t, query.q)
      // set init data
      let data: ZZ = {
        path: undefined,
        mime: undefined,
        buffer: undefined,
      }
      // 캐시파일에서 데이터 가져오기
      let _cacheFile = await libs.getCache(code, _imageOption)
      // 캐시 데이터 사용하기
      if (_imageOption?.query && await _cacheFile.exists())
      {
        const _cache = await _cacheFile.json()
        if (_cache)
        {
          const _path = _cache.cache_path || _cache.path
          if (await existFile(_path))
          {
            if (_cache.private) checkingToken(ctx, { usePublic: true })
            data.path = _path
            data.mime = _cache.mime
          }
          else
          {
            // 문제가 있는 파일이라고 판단하여 캐시파일을 삭제한다.
            await deleteFile(_cacheFile.name as string)
          }
        }
      }
      // 캐시파일에서 가져온 데이터가 없다면?
      if (!(data.path && data.mime))
      {
        // get file data
        const _file = db.getData({
          table: DB.TABLE.FILE,
          where: `code GLOB \'${code}\'`,
        }).data
        if (!_file)
        {
          throw new ServiceError('Not found File data.', { status: 404 })
        }
        if (!(await existFile(_file.path)))
        {
          throw new ServiceError('Not found file.', { status: 404 })
        }
        // get module data
        const _module = FileTool.getModuleData(_file.module, _file.module_srl)
        if (!_module)
        {
          throw new ServiceError('Not found module data.', { status: 500 })
        }
        // get permission
        const _permission = Permission.filter(_module.mode)
        // switching status
        switch (_permission)
        {
          case Permission.PRIVATE:
          case Permission.PUBLIC:
            // check auth
            if (_permission === Permission.PRIVATE) checkingToken(ctx, { usePublic: true })
            let _newData: ZZ = {}
            // get new data
            if (_file.mime?.startsWith('image/') && _imageOption)
            {
              _newData = await libs.resizeImage({
                code: _file.code,
                path: _file.path,
                mime: _file.mime,
                imageOptions: _imageOption,
              })
            }
            else
            {
              _newData = {
                path: _file.path,
                mime: _file.mime,
              }
            }
            // create cache file
            if (_imageOption?.query)
            {
              _cacheFile = await libs.getCache(_file.code, _imageOption)
              await libs.createCache(_cacheFile, {
                code: _file.code,
                module: _file.module,
                module_srt: _file.module_srl,
                private: _permission === Permission.PRIVATE,
                path: _file.path,
                cache_path: _newData.cachePath || null,
                name: libs.remakeFilename(_file.name, _newData.mime.split('/')[1]),
                mime: _newData.mime,
              })
            }
            // retry set data
            data.path = _newData.cachePath || _newData.path
            data.mime = _newData.mime
            if (_newData.buffer) data.buffer = _newData.buffer
            break
          case Permission.READY:
            data.path = _file.path
            data.mime = _file.mime
            break
          default:
            throw new ServiceError('Invalid permission.', { status: 500 })
        }
      }
      // check output data
      if (!(data.path && data.mime))
      {
        throw new ServiceError('Not found file data.', { status: 404 })
      }
      // 버퍼 데이터를 만든다.
      if (!data.buffer && data.path)
      {
        data.buffer = await libs.convertPathToBuffer(data.path)
      }
      // check buffer data
      if (!data.buffer)
      {
        throw new ServiceError('Not found buffer data.', { status: 404 })
      }
      // return
      return new Response(data.buffer, {
        headers: {
          'Content-Type': data.mime,
          'Content-Length': String(data.buffer.byteLength),
          'Cache-Control': !ctx.store.service.dev ? 'public, max-age=2592000' : 'no-cache',
        },
      })
    }
    catch (_e: any)
    {
      throw new ServiceError(_e.status === 404 ? 'File not found' : 'Failed open File', {
        status: _e.status || 404,
        text: _e.message,
        cause: _e,
      })
    }
  }

  static async putItem({ body, service }: PutItemParams)
  {
    let _path: string = ''
    try
    {
      // check exist origin data
      const originCount = db.getCount({
        table: FileTool.getModuleName(body.module),
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
      let _resource: FileToResource = await FileTool.fileToResource(body.file)
      // convert format or quality
      if (_resource.image)
      {
        _resource.image = libs.convertImageFormat(_resource.image, _resource.mime, body.quality)
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

  static async patchItem({ srl, body, service }: PatchItemParams)
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
        if (body.file.size > service.preference['file.limitSize'])
        {
          throw new ServiceError('File size limit exceeded.', { status: 400 })
        }
        // convert file to resource
        let _resource: FileToResource = await FileTool.fileToResource(body.file)
        // convert format or quality
        if (_resource.image)
        {
          _resource.image = libs.convertImageFormat(_resource.image, _resource.mime, body.quality)
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
        if (_resource.image)
        {
          const _metadata = await _resource.image.metadata()
          _ready.json.width = _metadata.width
          _ready.json.height = _metadata.height
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
      if (item.data.path) await deleteFile(item.data.path)
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

  static async deleteItem(srl: number)
  {
    console.log('File_.deleteItem()', srl)
  }

}
