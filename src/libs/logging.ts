import logixlysia from 'logixlysia'
import { IS_DEV, PATHS, getBool } from '@/libs/assets'
import { createRecordFileTransport } from '@/libs/logging-file'
import { parseJSON } from '@/libs/objects'
import { colorText, dateFormat } from '@/libs/strings'
import type { Transport } from 'logixlysia'

/**
 * # GUIDES
 * - LEVEL: 'DEBUG' | 'INFO' | 'WARNING' | 'ERROR'
 *
 * 콘솔 출력은 consoleTransport가, JSONL 파일 기록은 recordFileTransport가 담당한다.
 * 파일 생성, 회전, 보관 정책의 구현은 logging-file.ts에서 관리한다.
 */

const { SERVICE_NAME, LOG_RECORD, LOG_PRINT }: ENV = Bun.env

function getPositiveNumber(value: string | undefined, fallback: number): number
{
  const parsedValue = Number(value)
  return Number.isFinite(parsedValue) && parsedValue > 0 ? parsedValue : fallback
}

/**
 * 파일 로그 정책
 *
 * 환경변수를 지정하지 않으면 아래 기본값을 사용한다.
 * - LOG_RECORD_MAX_SIZE_MB: 파일 하나의 최대 크기(MB), 기본 10MB
 * - LOG_RECORD_RETENTION_DAYS: 로그 보관 기간(일), 기본 7일
 * - LOG_RECORD_MAX_FILES_PER_LEVEL: 레벨별 최대 파일 수, 기본 30개
 */
const recordFilePolicy = {
  directory: `${PATHS.DATA}/logs`,
  maxFileSizeBytes: Math.floor(
    getPositiveNumber(Bun.env.LOG_RECORD_MAX_SIZE_MB, 10) * 1024 * 1024,
  ),
  retentionDays: getPositiveNumber(Bun.env.LOG_RECORD_RETENTION_DAYS, 7),
  maxFilesPerLevel: Math.floor(
    getPositiveNumber(Bun.env.LOG_RECORD_MAX_FILES_PER_LEVEL, 30),
  ),
}

function getLevel(level: string): string
{
  switch (level)
  {
    case 'DEBUG':
      return `🐞 ${colorText('DEBUG', 'magenta')}`
    case 'INFO':
    default:
      return `ℹ️ ${colorText('INFO', 'green')}`
    case 'WARNING':
      return `⚠️ ${colorText('WARNING', 'yellow')}`
    case 'ERROR':
      return `🚨 ${colorText('ERROR', 'red')}`
  }
}
function getUrl(url: string): string
{
  const _url = new URL(url)
  return `${_url.pathname}${_url.search}`
}
function getStatus(code: number): string
{
  if (!code) return ''
  const _code = code.toString()
  switch (code)
  {
    case 200:
      return colorText(_code, 'green')
    case 204:
    case 404:
      return colorText(_code, 'blue')
    case 400:
    case 500:
      return colorText(_code, 'red')
    default:
      return _code
  }
}
function getMessage(code: number, msg: string): string
{
  if (!msg) return ''
  switch (code)
  {
    case 422:
      const obj = parseJSON(msg)
      // validation 원문에는 schema, 입력값, 전체 오류 목록이 포함되므로 대표 메시지만 출력한다.
      return `${obj?.message || obj?.summary || 'Request validation failed.'}`
    default:
      return msg || ''
  }
}
const consoleTransport: Transport = {
  async log(level, message, meta: ZZ = {})
  {
    if (!getBool(LOG_PRINT)) return
    meta.context = meta.context ?? {}
    if (meta.context.raw)
    {
      let _color: any
      switch (level)
      {
        case 'ERROR': _color = 'red'; break
        case 'WARNING': _color = 'yellow'; break
      }
      console.log(_color ? colorText(message, _color) : message)
    }
    else
    {
      const _level = getLevel(level)
      const _time = colorText(dateFormat(undefined, '{yyyy}-{MM}-{dd} {hh}:{mm}:{ss}.{ms}'), 'dark')
      const _method = `[${meta.request.method}]`
      const _url = colorText(getUrl(meta.request.url), 'blue')
      const _messages = [
        getStatus(meta.status),
        getMessage(meta.status, message),
        colorText(`${(Number(process.hrtime.bigint() - meta.beforeTime) / 1_000_000).toFixed(3)}ms`, 'dark'),
      ].filter(Boolean)
      console.group(`${_level} ${_time} ${_method} ${_url} || ${_messages.join(' || ')}`)
      const _countContext = Object.keys(meta.context).length
      let tree = Object.entries(meta.context).map(([ key, value ], k) => {
        return {
          key: colorText(key, 'cyan'),
          value: value,
        }
      })
      tree.forEach(({ key, value }, k) => {
        const _line = k+1 >= _countContext ? '└─' : '├─'
        console.log(`${_line} ${colorText(key, 'cyan')}  ${colorText(value as string, 'light')}`)
      })
      if (meta.error)
      {
        switch (meta.status)
        {
          case 204:
          case 401:
          case 404:
          case 422:
            // validation 오류의 stack에는 전체 schema와 입력값이 중복되어 출력된다.
            break
          default:
            console.error(meta.error.stack)
            break
        }
      }
      console.groupEnd()
    }
  },
}

const recordFileTransport: Transport = {
  log(level, message, meta = {})
  {
    if (!getBool(LOG_RECORD)) return
    const fileTransport = createRecordFileTransport({
      policy: recordFilePolicy,
      service: SERVICE_NAME,
    })
    return fileTransport.log(level, message, meta)
  },
}

const logging = logixlysia({
  preset: IS_DEV ? 'dev' : 'prod',
  config: {
    // Startup
    showStartupMessage: false,
    startupMessageFormat: 'banner',

    // Basic
    service: SERVICE_NAME,
    ip: true,
    autoRedact: true,
    showContextTree: true,
    logQueryParams: true,
    contextDepth: 2,
    slowThreshold: 500,
    verySlowThreshold: 1000,
    // requestId: true,

    // Output
    timestamp: {
      translateTime: 'yyyy-mm-dd HH:MM:ss.SSS',
    },
    logFilter: {
      level: IS_DEV ? [ 'DEBUG', 'INFO', 'ERROR', 'WARNING' ] : [ 'INFO', 'WARNING', 'ERROR' ],
      // status: IS_DEV ? undefined : [ 500, 501, 502, 503, 504 ],
    },
    useColors: true,
    customLogFormat: `{now} {level} {service} {icon} {method} {pathname} {status} {statusText} || {duration}`,
    // 콘솔에서 로그 출력 안되게 하기
    disableInternalLogger: !getBool(LOG_PRINT),
    // 파일로 로그 저장 안하게 하기
    disableFileLogging: !getBool(LOG_RECORD),

    // Transport
    useTransportsOnly: true,
    transports: [
      consoleTransport,
      recordFileTransport,
    ],
  },
})

export default logging
