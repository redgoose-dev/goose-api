import { PATHS } from '@/libs/assets'
import { createDirectory } from '@/libs/file'
import { getSharp } from '@/libs/external'

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
  if (!((_w && _w > minSize) || (_h && _h > minSize))) return undefined
  const arr: string[] = []
  if (_w && _w > minSize) arr.push(`w=${_w}`)
  if (_h && _h > minSize) arr.push(`h=${_h}`)
  if (_t) arr.push(`t=${_t}`)
  if (_q && _q > minQuality) arr.push(`q=${_q}`)
  return {
    query: arr.length > 0 ? arr.join('&') : undefined,
    object: { w: _w, h: _h, t: _t, q: _q },
  }
}

export async function getCache(code: string, options?: ZZ)
{
  const _filename = options?.query ? `${code}__${options.query}.json` : `${code}.json`
  const _path = `${PATHS.CACHE}/json/${_filename}`
  return Bun.file(_path)
}

export async function createCache(file: Bun.BunFile, data: ZZ)
{
  await file.write(JSON.stringify(data, null, 2))
}

/**
 * 이미지 리사이즈
 */
export async function resizeImage(op: ZZ = {})
{
  const { code, path, mime, imageOptions } = op
  // get dirname
  const dirName = path.split(`${PATHS.UPLOAD}/`)[1].split('/')[0]
  // set destination path
  let destPath = `${PATHS.CACHE}/${dirName}/${code}__${imageOptions.query}`
  let _buffer = await convertPathToBuffer(destPath)
  if (_buffer)
  {
    return {
      path: destPath,
      buffer: _buffer,
      mime: mime,
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
        })
        break
    }
    let _mime: string
    if (_q > 85)
    {
      _resize.webp({ quality: _q })
      _mime = 'image/webp'
    }
    else
    {
      _resize.jpeg({ quality: _q })
      _mime = 'image/jpeg'
    }
    // create buffer
    const _buffer = await _resize.toBuffer()
    // save file
    await createDirectory(destPath)
    await Bun.write(destPath, _buffer)
    return {
      cachePath: destPath,
      buffer: _buffer,
      mime: _mime,
    }
  }
}

export async function convertPathToBuffer(path: string): Promise<Buffer | null>
{
  const file = Bun.file(path)
  if (!await file.exists()) return null
  return Buffer.from(await file.arrayBuffer())
}

export function remakeFilename(path: string, ext: string): string
{
  if (!path) return ''
  const base = path.substring(0, path.lastIndexOf('.'))
  return `${base}.${ext}`
}
