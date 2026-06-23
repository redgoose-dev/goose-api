import { randomBytes } from 'node:crypto'

/**
 * 콘솔로그에서 컬러를 입힌다.
 */
type ColorTextColor = 'white' | 'black' | 'red' | 'green' | 'yellow' | 'blue' | 'magenta' | 'cyan'
export function colorText(message: string, color: ColorTextColor): string
{
  const assets: ZZ = {
    white: '\x1b[37m',
    black: '\x1b[31m',
    red: '\x1b[31m',
    green: '\x1b[32m',
    yellow: '\x1b[33m',
    blue: '\x1b[34m',
    magenta: '\x1b[35m',
    cyan: '\x1b[36m',
  }
  return assets[color] ? `${assets[color]}${message}\x1b[0m` : message
}

/**
 * 숫자 한자리라면 앞에 `0`을 붙인다.
 */
export function twoDigit(day: string|number): string
{
  return `0${day}`.slice(-2)
}

/**
 * convert date format
 * format guide: `{yyyy}-{MM}-{dd} / {month},{week},{weekShort} / {hh}:{mm}:{ss}`
 */
export function dateFormat(date: Date, format: string): string
{
  let mix = format.replace(/\{yyyy\}/, String(date.getFullYear()))
  mix = mix.replace(/\{MM\}/, twoDigit(date.getMonth() + 1))
  mix = mix.replace(/\{dd\}/, twoDigit(date.getDate()))
  mix = mix.replace(/\{hh\}/, twoDigit(date.getHours()))
  mix = mix.replace(/\{mm\}/, twoDigit(date.getMinutes()))
  mix = mix.replace(/\{ss\}/, twoDigit(date.getSeconds()))
  return mix
}

/**
 * get byte
 */
export function getByte(bytes: number): string
{
  const sizes = [ 'Bytes', 'KB', 'MB', 'GB', 'TB' ]
  if (bytes === 0) return '0 Byte'
  let i = Math.floor(Math.log(bytes) / Math.log(1024))
  return String(Math.round(bytes / Math.pow(1024, i))) + sizes[i]
}

/**
 * create code
 * @param {number} size
 */
export function createCode(size: number = 8): string
{
  return randomBytes(size).toString('hex')
}

/**
 * @example
 * ```ts
 * const str = printf('Hello {0} {1}', 'World', '!')
 * console.log(str) // Hello World !
 * ```
 */
export function printf(str: string, ...values: any[]): string
{
  for (let i = 0; i < values.length; i++)
  {
    let pattern = `\\{${i}\\}`
    let replace = new RegExp(pattern, 'g')
    str = str.replace(replace, values[i])
  }
  return str
}

/**
 * 객체나 배열을 URI로 인코딩한다.
 */
export function encodeUri(data?: ZZ | ZZ[]): string
{
  if (!data) return ''
  return Buffer.from(JSON.stringify(data), 'utf8').toString('base64url')
}

/**
 * base64 인코딩된 URI를 객체나 배열로 디코딩한다.
 */
export function decodeUri(base64?: string): ZZ | ZZ[] | null
{
  if (!base64) return null
  try
  {
    return JSON.parse(Buffer.from(base64, 'base64url').toString('utf8'))
  }
  catch
  {
    return null
  }
}

/**
 * 객체를 쿼리스트링으로 변환한다.
 */
export function parseQueryString(query: ZZ): string
{
  return new URLSearchParams(query).toString()
}
