import { exists, mkdir } from 'node:fs/promises'
import { PATHS, PATH_UPLOAD } from './assets'

/**
 * exist file
 */
export async function existFile(path: string): Promise<boolean>
{
  const file = Bun.file(path)
  return await file.exists()
}

export async function deleteFile(path: string)
{
  const _file = Bun.file(path)
  if (await _file.exists()) await _file.delete()
}

export async function getUploadPath(dirName: string = PATH_UPLOAD.ORIGIN, filename: string = '')
{
  const basePath = `${PATHS.UPLOAD}/${dirName}`
  const now = new Date()
  const year = now.getFullYear().toString()
  const month = String(now.getMonth() + 1).padStart(2, '0')
  const path = `${basePath}/${year}/${month}`
  if (!(await exists(path)))
  {
    await mkdir(path, { recursive: true })
  }
  if (filename) filename = `/${filename}`
  return path + filename
}
