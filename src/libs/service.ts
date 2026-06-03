import { createCode } from '@/libs/strings'
import { DEFAULT_HEADERS, HEADERS_KEYS } from './assets'
import { getElapsedTime, setHeaders } from './server'
import { filteringObject } from '@/libs/objects'

export function onRequest({ request, set, store }: any)
{
  // set error code
  request.errorCode = createCode(12)
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
    set.headers[HEADERS_KEYS.ERROR_CODE] = request.errorCode
    store.logger.mergeContext(request, filteringObject({
      code: request.errorCode,
      errorMessage: error.errorMessage,
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
  // 오류 스택이 있으면 출력하기, TODO: 로거 영역에서 출력 가능하다면 위치 옮기기
  if (_status !== 404 && service.dev)
  {
    console.error(error.stack)
  }
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
  return {
    srl: Number.isFinite(value) ? Number(value) : undefined,
    code: typeof value === 'string' ? value : undefined,
  }
}
