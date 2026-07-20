import { rename, stat } from 'node:fs/promises'
import DB, { db } from '@/classes/DB'
import { PATHS } from '@/libs/assets'
import { createDirectory, deleteFile } from '@/libs/file'
import { getSharp } from '@/libs/external'
import type { BaseModel } from '@/libs/models'

export const MODULE = {
  ARTICLE: 'article',
  CHECKLIST: 'checklist',
  COMMENT: 'comment',
  JSON: 'json',
}

export function getModuleName(module: string): string
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
export type FileToResource = {
  name: string
  mime: string
  ext: string
  size: number
  bytes?: Uint8Array
  image?: Bun.Image
}
export async function fileToResource(file: File): Promise<FileToResource>
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

export function count({ table, where, values }: BaseModel['paramsTableSelect']): number
{
  const count = db.getCount({
    table: table ?? DB.TABLE.FILE,
    where: where ?? '',
    values: values ?? {},
  })
  return count.data || 0
}

type GetIndexParams = {
  module: string
  module_srl: number
  field: string
}
export function getIndex({ module, module_srl, field }: GetIndexParams)
{
  const index = db.getIndex({
    table: DB.TABLE.FILE,
    field,
    where: [
      `AND module LIKE \'${module}\'`,
      `AND module_srl = ${module_srl}`,
    ],
  })
  return index.data
}

export function getModuleData(module: string, moduleSrl: number): ZZ | null
{
  const _module = getModuleName(module)
  if (!_module) return null
  return db.getData({
    table: _module,
    where: `srl = ${moduleSrl}`,
  }).data
}

/**
 * 이미지를 다른 이미지 포맷과 퀄리티로 변환한다.
 */
export function convertImageFormat(image: Bun.Image, format?: string, quality?: number)
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

export function getImageOptions(_w?: number, _h?: number, _t?: string, _q?: number): ZZ | undefined
{
  const minSize = 50
  const minQuality = 0
  const width = _w && _w > minSize ? _w : undefined
  const height = _h && _h > minSize ? _h : undefined
  if (!(width || height)) return undefined

  // 실제 변환에 사용되는 기본값까지 캐시 키에 포함하여
  // `t=contain`/생략, `q=90`/생략이 서로 다른 캐시를 만들지 않도록 한다.
  const type = _t || 'contain'
  const quality = _q && _q > minQuality ? _q : 90
  const arr: string[] = []
  if (width) arr.push(`w=${width}`)
  if (height) arr.push(`h=${height}`)
  arr.push(`t=${type}`)
  arr.push(`q=${quality}`)
  return {
    query: arr.length > 0 ? arr.join('&') : undefined,
    object: { w: width, h: height, t: type, q: quality },
  }
}

export function getCache(code: string)
{
  const _path = `${PATHS.CACHE}/json/${code}.json`
  return Bun.file(_path)
}

async function writeAtomically(path: string, data: string | Uint8Array)
{
  const temporaryPath = `${path}.${Date.now()}-${Math.random().toString(36).slice(2)}.tmp`
  try
  {
    await Bun.write(temporaryPath, data)
    await rename(temporaryPath, path)
  }
  catch (_e)
  {
    try { await deleteFile(temporaryPath) } catch {}
    throw _e
  }
}

export async function createCache(file: Bun.BunFile, data: ZZ)
{
  // 메타데이터는 작으므로 불필요한 공백을 제거한다.
  await writeAtomically(file.name as string, JSON.stringify(data))
}

/**
 * 이미지 리사이즈
 */
export async function resizeImage(op: ZZ = {})
{
  const { code, path, mime, imageOptions, save } = op
  // get dirname
  const dirName = path.split(`${PATHS.UPLOAD}/`)[1].split('/')[0]
  // set destination path
  let destPath = `${PATHS.CACHE}/${dirName}/${code}__${imageOptions.query}`
  const destFile = Bun.file(destPath)
  if (save && await destFile.exists())
  {
    return {
      path: destPath,
      // resizeImage()은 항상 WebP로 저장한다.
      mime: 'image/webp',
      cachePath: destPath,
    }
  }
  else
  {
    // set resource
    const _w: number = imageOptions.object.w
    const _h: number = imageOptions.object.h
    const _t: string = imageOptions.object.t || 'contain'
    const _q: number = imageOptions.object.q || 90
    // run resize
    const sharp = await getSharp()
    const _resize = sharp(path)
    switch (_t)
    {
      case 'cover':
      case 'contain':
      case 'fill':
      case 'inside':
      case 'outside':
        _resize.resize({
          width: _w,
          height: _h,
          fit: _t,
          kernel: 'lanczos3',
          background: { r: 255, g: 255, b: 255, alpha: 0 },
        })
        break
    }
    let _mime = 'image/webp'
    _resize.webp({ quality: _q })
    // create buffer
    const _buffer = await _resize.toBuffer()
    // save file
    if (save)
    {
      await createDirectory(destPath)
      await writeAtomically(destPath, _buffer)
    }
    return {
      cachePath: save ? destPath : undefined,
      buffer: _buffer,
      mime: _mime,
    }
  }
}

export async function remove({ module, module_srl }: ZZ)
{
  const _where = [
    `AND module LIKE \'${module}\'`,
    `AND module_srl = ${module_srl}`,
  ]
  // get item
  const files = db.getIndex({
    table: DB.TABLE.FILE,
    field: 'srl,code,path',
    where: _where,
  })
  // delete data
  db.deleteData({
    table: DB.TABLE.FILE,
    where: _where,
  })
  // delete files
  for (const file of files.data)
  {
    await deleteFile(file.path)
    await deleteCache(file.code)
  }
}

export async function deleteCache(code: string)
{
  const pattern = `${PATHS.CACHE}/**/${code}*`
  const glob = new Bun.Glob(pattern)
  for await (const file of glob.scan('.'))
  {
    await deleteFile(file)
  }
}

export type CleanupCacheOptions = {
  maxAgeDays?: number
  dryRun?: boolean
}

export type CleanupCacheResult = {
  scanned: number
  candidates: string[]
  deleted: number
}

/**
 * 오래된 변환 캐시와 현재 사용하지 않는 쿼리별 JSON 캐시를 정리한다.
 * 코드별 기본 JSON은 DB 조회를 줄이는 메타데이터이므로 보존한다.
 */
export async function cleanupCache(op: CleanupCacheOptions = {}): Promise<CleanupCacheResult>
{
  const maxAgeDays = op.maxAgeDays ?? 30
  const dryRun = op.dryRun ?? true
  if (!Number.isFinite(maxAgeDays) || maxAgeDays <= 0)
  {
    throw new Error('maxAgeDays must be greater than 0.')
  }

  const expiredAt = Date.now() - (maxAgeDays * 24 * 60 * 60 * 1000)
  const temporaryExpiredAt = Date.now() - (24 * 60 * 60 * 1000)
  const glob = new Bun.Glob(`${PATHS.CACHE}/**/*`)
  const candidates: string[] = []
  let scanned = 0

  for await (const file of glob.scan('.'))
  {
    let information
    try
    {
      information = await stat(file)
    }
    catch
    {
      continue
    }
    if (!information.isFile()) continue
    scanned++

    const normalizedPath = file.replaceAll('\\', '/')
    const filename = normalizedPath.split('/').pop() || ''
    const isLegacyQueryJson = normalizedPath.includes('/json/')
      && filename.includes('__')
      && filename.endsWith('.json')
    const isVariant = filename.includes('__')
      && !filename.endsWith('.json')
      && !filename.endsWith('.tmp')
    const isTemporary = filename.endsWith('.tmp')
    const isExpired = isLegacyQueryJson
      || (isVariant && information.mtimeMs < expiredAt)
      || (isTemporary && information.mtimeMs < temporaryExpiredAt)

    if (isExpired) candidates.push(file)
  }

  let deleted = 0
  if (!dryRun)
  {
    for (const file of candidates)
    {
      try
      {
        await deleteFile(file)
        deleted++
      }
      catch
      {
        // 다른 프로세스가 먼저 삭제한 경우에는 계속 진행한다.
      }
    }
  }

  return {
    scanned,
    candidates,
    deleted,
  }
}
