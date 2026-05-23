const { NODE_ENV } = Bun.env

export const IS_DEV = NODE_ENV !== 'production'

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
