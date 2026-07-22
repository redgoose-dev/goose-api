import { createCode } from '@/libs/strings'
import { DEFAULT_HEADERS, HEADERS_KEYS } from './assets'
import { getElapsedTime, setHeaders } from './server'
import { filteringObject } from '@/libs/objects'

export function onRequest({ request }: any)
{
  request.errorCode ??= createCode(12)
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
  if (![ 204, 403, 404 ].includes(error.status))
  {
    // onRequest 자체에서 오류가 발생한 경우에도 오류 코드가 누락되지 않도록 보완한다.
    const errorCode = request.errorCode ??= createCode(12)
    set.headers[HEADERS_KEYS.ERROR_CODE] = errorCode
    store.logger.mergeContext(request, filteringObject({
      error_code: errorCode,
      error_message: error.errorMessage,
    }))
  }
}
export function onErrorAfter({ request, set, store, error }: any): Response
{
  const { service } = store
  const _status = error?.status || 500
  let headers: ZZ = {
    ...DEFAULT_HEADERS,
    [HEADERS_KEYS.CONTENT_TYPE]: 'text/plain',
  }
  // 처리시간
  if (store.beforeTime)
  {
    headers[HEADERS_KEYS.PROCESS_TIME] = getElapsedTime(store.beforeTime)
  }
  // 오류 스택 출력하기
  // if (_status !== 404 && error.stack && service.dev)
  // {
  //   store.logger.error(request, error.stack, { raw: true })
  // }
  // set headers
  setHeaders(set.headers, headers)
  // switch status
  switch (_status)
  {
    case 301:
    case 302:
      return Response.redirect(error?.message || '', _status)
    case 401:
      return new Response('Unauthorized', {
        status: _status,
      })
    case 422:
      return new Response('Invalid request data.', {
        status: _status,
      })
    default:
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
  const isNumeric = (str: string) => /^\d+$/.test(str)
  if (typeof value === 'number')
  {
    return {
      srl: Number.isFinite(value) ? value : undefined,
      code: undefined,
    }
  }
  const trimmed = value.trim()
  return {
    srl: isNumeric(trimmed) ? Number(trimmed) : undefined,
    code: isNumeric(trimmed) ? undefined : value,
  }
}
