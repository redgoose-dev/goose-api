import { createCode } from '@/libs/strings'
import { DEFAULT_HEADERS, HEADERS_KEYS } from './assets'
import { getElapsedTime, setHeaders } from './server'

export function onRequest({ request, set, store }: any)
{
  // console.log('call onRequest()')
}

export function onResponse({ set, store }: any)
{
  let headers: ZZ = { ...DEFAULT_HEADERS }
  // 처리시간
  if (store.beforeTime)
  {
    headers[HEADERS_KEYS.PROCESS_TIME] = getElapsedTime(store.beforeTime)
  }
  // set headers
  setHeaders(set.headers, headers)
}

export function onErrorBefore({ request, set, store, error }: any)
{
  // set error code
  if (![ 403, 404 ].includes(error.status))
  {
    const errorCode = createCode(12)
    set.headers[HEADERS_KEYS.ERROR_CODE] = errorCode
    store.logger.mergeContext(request, {
      code: errorCode,
    })
  }
}
export function onErrorAfter({ request, set, store, error }: any): Response
{
  const _status = error?.status || 500
  let headers: ZZ = { ...DEFAULT_HEADERS }
  // 처리시간
  if (store.beforeTime)
  {
    headers[HEADERS_KEYS.PROCESS_TIME] = getElapsedTime(store.beforeTime)
  }
  // set headers
  setHeaders(set.headers, headers)
  // switch status
  switch (_status)
  {
    case 301:
    case 302:
      return Response.redirect(error?.message || '', _status)
    default:
      set.headers[HEADERS_KEYS.CONTENT_TYPE] = 'text/plain'
      return new Response(error?.message || 'Invalid Error', {
        status: _status,
      })
  }
}

type TypeClassifySrlCode = {
  srl?: number
  code?: string
}
export function classifySrlCode(value: number | string): TypeClassifySrlCode
{
  return {
    srl: Number.isFinite(value) ? Number(value) : undefined,
    code: typeof value === 'string' ? value : undefined,
  }
}
