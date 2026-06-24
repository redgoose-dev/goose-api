import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { Permission } from '@/libs/assets'
import { checkingToken } from '@/libs/verify'
import { deleteFile, existFile } from '@/libs/file'
import * as helper from './__helper'
import type { FileModel } from './__model'

type GetItemParams = {
  code: string
  query: FileModel['getItemQuery']
  ctx: any
}

export default async function getIndex({ code, query, ctx }: GetItemParams)
{
  try
  {
    // set image options
    const _imageOption = helper.getImageOptions(query.w, query.h, query.t, query.q)

    // set init data
    let data: ZZ = {
      path: undefined,
      mime: undefined,
      buffer: undefined,
    }

    // 캐시파일에서 데이터 가져오기
    let _cacheFile = await helper.getCache(code, _imageOption)

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
      const _module = helper.getModuleData(_file.module, _file.module_srl)
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
            _newData = await helper.resizeImage({
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
            _cacheFile = await helper.getCache(_file.code, _imageOption)
            await helper.createCache(_cacheFile, {
              code: _file.code,
              module: _file.module,
              module_srl: _file.module_srl,
              private: _permission === Permission.PRIVATE,
              path: _file.path,
              cache_path: _newData.cachePath || null,
              name: helper.remakeFilename(_file.name, _newData.mime.split('/')[1]),
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
      data.buffer = await helper.convertPathToBuffer(data.path)
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
