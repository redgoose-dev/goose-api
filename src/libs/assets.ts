const { PATH_BASE, PATH_DATA, PATH_URL, DEBUG } = Bun.env as ENV

export const IS_DEV = getBool(DEBUG)

export const PATHS = {
  BASE: PATH_BASE || '.',
  DATA: PATH_DATA || './data',
  UPLOAD: PATH_DATA ? `${PATH_DATA}/upload` : './data/upload',
  CACHE: PATH_DATA ? `${PATH_DATA}/cache` : './data/cache',
  URL: PATH_URL || 'http://localhost',
}
export type Paths = typeof PATHS[keyof typeof PATHS]

export const LOG_RECORD_DB_POLICY = {
  BATCH_SIZE: 50,
  FLUSH_INTERVAL_MS: 1000,
  MAX_QUEUE_SIZE: 5000,
} as const

export const PATH_UPLOAD = {
  COVER: 'cover',
  ORIGIN: 'origin',
}

export const HEADERS_KEYS = {
  PROCESS_TIME: 'X-Process-Time',
  MAX_AGE: 'Access-Control-Max-Age',
  ALLOW_ORIGIN: 'Access-Control-Allow-Origin',
  ALLOW_METHODS: 'Access-Control-Allow-Methods',
  ALLOW_HEADERS: 'Access-Control-Allow-Headers',
  ALLOW_CREDENTIALS: 'Access-Control-Allow-Credentials',
  EXPOSE_HEADERS: 'Access-Control-Expose-Headers',
  CONTENT_TYPE: 'Content-Type',
  REQUEST_ID: 'X-Request-ID',
}

export const DEFAULT_HEADERS = {
  'Server': 'bun',
  [HEADERS_KEYS.ALLOW_ORIGIN]: '*',
  [HEADERS_KEYS.ALLOW_METHODS]: 'GET, POST, PUT, PATCH, DELETE',
  [HEADERS_KEYS.ALLOW_HEADERS]: `Origin, Content-Type, Authorization, Accept, ${HEADERS_KEYS.REQUEST_ID}`,
  [HEADERS_KEYS.ALLOW_CREDENTIALS]: 'true',
  [HEADERS_KEYS.EXPOSE_HEADERS]: HEADERS_KEYS.REQUEST_ID,
}

export const WS_TIMEOUT = 120 // 웹소켓 타임아웃 (초)

export const RESIZE_TYPE = {
  COVER: 'cover',
  CONTAIN: 'contain',
  FILL: 'fill',
  INSIDE: 'inside',
  OUTSIDE: 'outside',
}

export abstract class Permission {
  static PUBLIC = 'public'
  static PRIVATE = 'private'
  static READY = 'ready'
  static filter(value?: string): string
  {
    switch (value)
    {
      case this.PUBLIC:
        return this.PUBLIC
      case this.PRIVATE:
        return this.PRIVATE
      case this.READY:
        return this.READY
      default:
        return this.PUBLIC
    }
  }
}

export function getBool(value?: string): boolean
{
  return (value ?? '').toLowerCase() === 'true'
}
