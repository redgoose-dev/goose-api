import { DEFAULT_HEADERS, HEADERS_KEYS } from './assets'
import { getElapsedTime, setHeaders } from './server'

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
