import logixlysia from 'logixlysia'
import type { Transport } from 'logixlysia'
import ServiceError from '@/classes/ServiceError'
import { IS_DEV, getBool } from '@/libs/assets'
import { colorText, dateFormat } from '@/libs/strings'

/**
 * TODO: 2026-05-23
 * 일단 기초적인 설정은 해두었고, 어떤 기능이 있는지 기초적인 수준에서는 파악했다.
 * 하지만 개선해야 할 부분은 많이 보이며 나중에 개선해야 할것이다.
 * - 오류가 났을때 좀 더 상세한 내용이 출력되어야 한다.
 * - 일단 파일로 저장하는 기능은 꺼두었지만 나중에 파일로 저장하는 기능을 넣어야 할것이다. 파일로 저장되는 오류 로그는 오류 코드와 함께 좀더 자세하게 기록되어야 할것이다.
 * - 시작부터 로그 기능에 붙집힐 순 없기 때문에 일단 엔드포인트 개발하면서 아이디어들을 축척해야할것이다.
 */

/**
 * # GUIDES
 *
 * - LEVEL: 'DEBUG' | 'INFO' | 'WARNING' | 'ERROR'
 */

const { SERVICE_NAME, PATH_DATA, LOG_RECORD, LOG_PRINT }: ENV = Bun.env

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
      const _status = meta.status ?? 500
      const _message = message ? ` || ${message}` : ''
      const _speed = colorText(`${(Number(process.hrtime.bigint() - meta.beforeTime) / 1_000_000).toFixed(3)}ms`, 'green')
      console.group(`${_level} ${_time} ${_method} ${_url} || ${_status + _message} || ${_speed}`)
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
      if (meta.error) console.error(meta.error.stack)
      console.groupEnd()
    }
  },
}

const recordFileTransport: Transport = {
  async log(level, message, meta = {})
  {
    if (!getBool(LOG_RECORD)) return
    // TODO: 파일로 기록하기
    console.warn('logixlysia.recordFile()', )
  }
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

    // File
    logFilePath: `${PATH_DATA}/logs/access.log`,
    logRotation: {
      maxSize: '10m',
      interval: '1d',
      maxFiles: '7d',
      compress: true,
      compression: 'gzip',
    },

    // Output
    timestamp: {
      translateTime: 'yyyy-mm-dd HH:MM:ss.SSS',
    },
    logFilter: {
      level: IS_DEV ? [ 'DEBUG', 'INFO', 'ERROR', 'WARNING' ] : [ 'WARNING', 'ERROR' ],
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
