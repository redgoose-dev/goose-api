const { NODE_ENV, PATH_BASE, PATH_DATA, PATH_URL } = Bun.env as ENV

export const IS_DEV = NODE_ENV !== 'production'

export const PATHS = {
  BASE: PATH_BASE || '.',
  DATA: PATH_DATA || './data',
  UPLOAD: PATH_DATA ? `${PATH_DATA}/upload` : './data/upload',
  CACHE: PATH_DATA ? `${PATH_DATA}/cache` : './data/cache',
  URL: PATH_URL || 'http://localhost',
}
export type Paths = typeof PATHS[keyof typeof PATHS]

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
  CONTENT_TYPE: 'Content-Type',
  ERROR_CODE: 'Error-Code',
}

export const DEFAULT_HEADERS = {
  'Server': 'bun',
  [HEADERS_KEYS.ALLOW_ORIGIN]: '*',
  [HEADERS_KEYS.ALLOW_METHODS]: 'GET, POST, PUT, PATCH, DELETE',
  [HEADERS_KEYS.ALLOW_HEADERS]: 'Origin, Content-Type, Authorization, Accept',
  [HEADERS_KEYS.ALLOW_CREDENTIALS]: 'true',
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
