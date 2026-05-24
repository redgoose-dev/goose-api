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
 * @param {number} len
 * @return {string}
 */
export function createCode(len: number = 8): string
{
  return randomBytes(len).toString('hex')
}

/**
 * date formatter
 * TODO: 사용하는지 확인 필요하다.
 */
export function dateFormatter(date: Date, options: ZZ = {}): string
{
  const formatter = new Intl.DateTimeFormat('ko-KR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: 'numeric',
    second: 'numeric',
    ...options,
  })
  return formatter.format(date)
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
