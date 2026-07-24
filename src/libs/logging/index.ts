import logixlysia from 'logixlysia'
import { IS_DEV, LOG_RECORD_DB_POLICY, PATHS, getBool } from '@/libs/assets'
import { createRecordDatabaseTransport } from '@/libs/logging/database'
import { parseJSON } from '@/libs/objects'
import { colorText, dateFormat } from '@/libs/strings'
import type { Transport } from 'logixlysia'

/**
 * # GUIDES
 * - LEVEL: 'DEBUG' | 'INFO' | 'WARNING' | 'ERROR'
 *
 * 콘솔 출력은 consoleTransport가, SQLite 기록은 recordDBTransport가 담당한다.
 * 기록 대기열은 logging/database.ts가, 연결과 스키마는 DB_Log.ts가 관리한다.
 */

const { SERVICE_NAME, LOG_RECORD, LOG_PRINT }: ENV = Bun.env

function getPositiveNumber(value: string | undefined, fallback: number): number
{
  const parsedValue = Number(value)
  return Number.isFinite(parsedValue) && parsedValue > 0 ? parsedValue : fallback
}

/**
 * SQLite 로그 정책
 *
 * 데이터베이스는 첫 기록이 발생할 때 `${PATHS.DATA}/log.sqlite`에 생성한다.
 * 정책 값은 assets.ts의 LOG_RECORD_DB_POLICY에서 관리한다.
 */
const recordDatabasePolicy = {
  path: `${PATHS.DATA}/log.sqlite`,
  retentionDays: getPositiveNumber(Bun.env.LOG_RECORD_RETENTION_DAYS, 7),
  batchSize: LOG_RECORD_DB_POLICY.BATCH_SIZE,
  flushIntervalMs: LOG_RECORD_DB_POLICY.FLUSH_INTERVAL_MS,
  maxQueueSize: LOG_RECORD_DB_POLICY.MAX_QUEUE_SIZE,
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

const databaseTransport = createRecordDatabaseTransport({
  policy: recordDatabasePolicy,
})
const recordDBTransport: Transport = {
  log(level, message, meta = {})
  {
    if (!getBool(LOG_RECORD)) return
    return databaseTransport.log(level, message, meta)
  }
}

export async function flushLogging(): Promise<void>
{
  await databaseTransport.flush()
}

export async function closeLogging(): Promise<void>
{
  await databaseTransport.close()
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
    // Logixlysia 내장 파일 기록은 사용하지 않는다.
    disableFileLogging: true,

    // Transport
    useTransportsOnly: true,
    transports: [
      consoleTransport,
      recordDBTransport,
    ],
  },
})

export default logging
