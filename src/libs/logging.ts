import logixlysia, {type Transport} from 'logixlysia'
import { IS_DEV } from '@/libs/assets'

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
    contextDepth: 2,
    slowThreshold: 500,
    verySlowThreshold: 1000,
    logQueryParams: false,

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
    },
    useColors: true,
    customLogFormat: `{now} {level} {service} {icon} {method} {pathname} {status} {statusText} || {duration}`,

    // Transport
    useTransportsOnly: false,
    transports: [
      // customLogFile,
    ],
  },
})

export default logging
