import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import { Permission } from '@/libs/assets'
import { checkingToken } from '@/libs/verify'
import { deleteFile, existFile } from '@/libs/file'
import * as helper from './__helper'
import type { FileModel } from './__model'

type GetItemParams = {
  srl?: number
  code?: string
  query: FileModel['getItemQuery']
  ctx: any
}

type GetResponseDataParams = {
  code: string
  path: string
  mime: string
  imageOption?: ZZ
  save: boolean
}

async function getResponseData({ code, path, mime, imageOption, save }: GetResponseDataParams): Promise<ZZ>
{
  if (mime.startsWith('image/') && imageOption)
  {
    return await helper.resizeImage({
      code,
      path,
      mime,
      imageOptions: imageOption,
      save,
    })
  }
  return { path, mime }
}

export default async function getItem({ srl, code, query, ctx }: GetItemParams)
{
  try
  {
    // 숫자(srl) 요청은 캐시를 사용하지 않고, 코드 요청만 메타데이터 캐시를 사용한다.
    const useCache = code !== undefined
    let cacheFile: Bun.BunFile | undefined
    let isPrivate = false
    let isPublic = false

    // set image options
    const imageOption = helper.getImageOptions(query.w, query.h, query.t, query.q)

    // 응답 데이터. 변환 직후에는 buffer를 사용하고, 그 외에는 파일을 스트리밍한다.
    const data: ZZ = {
      path: undefined,
      mime: undefined,
      buffer: undefined,
    }

    // 코드 요청은 JSON 캐시에서 파일 경로와 권한 정보를 먼저 확인한다.
    // 이미지 옵션이 없더라도 `${code}.json`을 읽어 DB 조회를 줄인다.
    if (useCache && code !== undefined)
    {
      cacheFile = helper.getCache(code)
      if (await cacheFile.exists())
      {
        let cache: ZZ | undefined
        try
        {
          cache = await cacheFile.json() as ZZ
        }
        catch
        {
          // 쓰기 중 중단되었거나 손상된 캐시는 원본 조회로 복구한다.
          try { await deleteFile(cacheFile.name as string) } catch {}
        }

        if (cache)
        {
          const validCache = typeof cache.path === 'string'
            && cache.path.length > 0
            && typeof cache.mime === 'string'
            && cache.mime.length > 0
            && typeof cache.private === 'boolean'

          if (!validCache)
          {
            try { await deleteFile(cacheFile.name as string) } catch {}
          }
          else if (await existFile(cache.path))
          {
            isPrivate = cache.private
            isPublic = !isPrivate
            if (isPrivate) checkingToken(ctx)

            const newData = await getResponseData({
              code,
              path: cache.path,
              mime: cache.mime,
              imageOption,
              save: true,
            })
            data.path = newData.cachePath || newData.path
            data.mime = newData.mime
            if (newData.buffer) data.buffer = newData.buffer
          }
          else
          {
            // 메타데이터는 있지만 실제 파일이 삭제된 경우 캐시를 재생성한다.
            try { await deleteFile(cacheFile.name as string) } catch {}
          }
        }
      }
    }

    // 캐시 적중에 실패한 경우에만 DB와 모듈 권한을 확인한다.
    if (!(data.path && data.mime))
    {
      const where: string[] = []
      const values: ZZ = {}
      if (srl !== undefined)
      {
        where.push('AND srl = $srl')
        values.$srl = srl
      }
      if (code !== undefined)
      {
        // code는 unique 값이므로 GLOB 대신 정확한 파라미터 검색을 사용한다.
        where.push('AND code = $code')
        values.$code = code
      }
      const file = db.getData({
        table: DB.TABLE.FILE,
        field: 'code,name,path,mime,module,module_srl',
        where,
        values,
      }).data
      if (!file)
      {
        throw new ServiceError('Not found File data.', { status: 404 })
      }
      if (!(await existFile(file.path)))
      {
        throw new ServiceError('Not found file.', { status: 404 })
      }
      // 캐시에는 모듈의 권한 결과도 저장하므로 캐시 적중 시 이 조회를 생략할 수 있다.
      const module = helper.getModuleData(file.module, file.module_srl)
      if (!module)
      {
        throw new ServiceError('Not found module data.', { status: 500 })
      }
      const permission = Permission.filter(module.mode)
      switch (permission)
      {
        case Permission.PRIVATE:
        case Permission.PUBLIC:
          isPrivate = permission === Permission.PRIVATE
          isPublic = permission === Permission.PUBLIC
          if (isPrivate) checkingToken(ctx)
          // 숫자 요청은 저장된 변환 캐시도 사용하지 않는다.
          const newData = await getResponseData({
            code: file.code,
            path: file.path,
            mime: file.mime,
            imageOption,
            save: useCache,
          })
          if (useCache)
          {
            cacheFile = helper.getCache(file.code)
            await helper.createCache(cacheFile, {
              code: file.code,
              module: file.module,
              module_srl: file.module_srl,
              private: isPrivate,
              path: file.path,
              name: file.name,
              mime: file.mime,
            })
          }

          data.path = newData.cachePath || newData.path
          data.mime = newData.mime
          if (newData.buffer) data.buffer = newData.buffer
          break

        case Permission.READY:
          // 준비 상태 파일은 캐시하지 않고 응답도 공유 캐시하지 않는다.
          data.path = file.path
          data.mime = file.mime
          break

        default:
          throw new ServiceError('Invalid permission.', { status: 500 })
      }
    }

    let body: Buffer | Bun.BunFile | undefined
    let contentLength: number | undefined
    if (data.buffer)
    {
      body = data.buffer
      contentLength = data.buffer.byteLength
    }
    else if (data.path)
    {
      // 원본 파일과 기존 변환 파일은 전체를 Buffer로 읽지 않고 스트리밍한다.
      const bodyFile = Bun.file(data.path)
      if (!(await bodyFile.exists()))
      {
        throw new ServiceError('Not found buffer data.', { status: 404 })
      }
      body = bodyFile
      contentLength = bodyFile.size
    }

    if (!body || !data.mime)
    {
      throw new ServiceError('Not found buffer data.', { status: 404 })
    }

    const cacheControl = isPrivate
      ? 'private, no-store'
      : (isPublic ? (!ctx.store.service.dev ? 'public, max-age=2592000' : 'no-cache') : 'no-store')

    return new Response(body, {
      headers: {
        'Content-Type': data.mime,
        'Content-Length': String(contentLength),
        'Cache-Control': cacheControl,
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
