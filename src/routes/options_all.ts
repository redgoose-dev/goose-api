import { Elysia } from 'elysia'
import { DEFAULT_HEADERS, HEADERS_KEYS } from '@/libs/assets'
import { getElapsedTime, setHeaders } from '@/libs/server'

const optionsAll = new Elysia({ name: 'method-options' })

optionsAll.onRequest(({ request, set, store }: any) => {
  if (request.method !== 'OPTIONS') return
  let headers: ZZ = {
    ...DEFAULT_HEADERS,
    [HEADERS_KEYS.ALLOW_METHODS]: 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
    [HEADERS_KEYS.PROCESS_TIME]: getElapsedTime(store.beforeTime),
    [HEADERS_KEYS.MAX_AGE]: '86400',
  }
  // set headers
  setHeaders(set.headers, headers)
  // return response
  return new Response(null, { status: 204 })
})

export default optionsAll
