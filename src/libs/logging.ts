import logixlysia, {type Transport} from 'logixlysia'
import { IS_DEV } from '@/libs/assets'

/**
 * TODO: 2026-05-23
 * 일단 기초적인 설정은 해두었고, 어떤 기능이 있는지 기초적인 수준에서는 파악했다.
 * 하지만 개선해야 할 부분은 많이 보이며 나중에 개선해야 할것이다.
 * - 오류가 났을때 좀 더 상세한 내용이 출력되어야 한다.
 * - 일단 파일로 저장하는 기능은 꺼두었지만 나중에 파일로 저장하는 기능을 넣어야 할것이다. 파일로 저장되는 오류 로그는 오류 코드와 함께 좀더 자세하게 기록되어야 할것이다.
 * - 시작부터 로그 기능에 붙집힐 순 없기 때문에 일단 엔드포인트 개발하면서 아이디어들을 축척해야할것이다.
 */

const { SERVICE_NAME, PATH_DATA }: ENV = Bun.env

const customLogFile: Transport = {
  async log(level, message, meta = {})
  {
    // console.log('customLogFile.log()', level, message, meta)
  },
}

const logging = logixlysia({
  // preset: 'prod',
  config: {
    // Startup
    showStartupMessage: false,
    startupMessageFormat: 'banner',

    // Basic
    service: SERVICE_NAME,
    ip: true,
    autoRedact: true,
    showContextTree: true,
    logQueryParams: false,
    contextDepth: 2,
    slowThreshold: 500,
    verySlowThreshold: 1000,

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
    disableInternalLogger: false, // 콘솔에서 로그 출력 안되게 하기
    disableFileLogging: true, // 파일로 로그 저장 안하게 하기

    // Transport
    useTransportsOnly: false,
    transports: [
      // customLogFile,
    ],
  },
})

export default logging
