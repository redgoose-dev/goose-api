import { checkKeysExist, getValueDict } from '@/libs/objects'
import type { MixModel } from './__model'

// route modules
import homeGetIndex from '@/routes/home/get-index'
import appGetIndex from '@/routes/app/get-index'
import appGetItem from '@/routes/app/get-item'
import articleGetIndex from '@/routes/article/get-index'
import articleGetItem from '@/routes/article/get-item'
import articlePutItem from '@/routes/article/put-item'
import categoryGetIndex from '@/routes/category/get-index'
import categoryGetItem from '@/routes/category/get-item'
import checklistGetIndex from '@/routes/checklist/get-index'
import checklistGetItem from '@/routes/checklist/get-item'
import commentGetIndex from '@/routes/comment/get-index'
import commentGetItem from '@/routes/comment/get-item'
import fileGetIndex from '@/routes/file/get-index'
import jsonGetIndex from '@/routes/json/get-index'
import jsonGetItem from '@/routes/json/get-item'
import nestGetIndex from '@/routes/nest/get-index'
import nestGetItem from '@/routes/nest/get-item'
import tagGetIndex from '@/routes/tag/get-index'

// route maps
const routeMap = new Map<string, Function | null>([
  // home
  [ 'get /', homeGetIndex ],
  // app
  [ 'get /app/', appGetIndex ],
  [ 'get /app/{srl}/', appGetItem ],
  // article
  [ 'get /article/', articleGetIndex ],
  [ 'get /article/{srl}/', articleGetItem ],
  [ 'put /article/', articlePutItem ],
  // category
  [ 'get /category/', categoryGetIndex ],
  [ 'get /category/{srl}/', categoryGetItem ],
  // checklist
  [ 'get /checklist/', checklistGetIndex ],
  [ 'get /checklist/{srl}/', checklistGetItem ],
  // comment
  [ 'get /comment/', commentGetIndex ],
  [ 'get /comment/{srl}/', commentGetItem ],
  // file
  [ 'get /file/', fileGetIndex ],
  // json
  [ 'get /json/', jsonGetIndex ],
  [ 'get /json/{srl}/', jsonGetItem ],
  // nest
  [ 'get /nest/', nestGetIndex ],
  [ 'get /nest/{srl}/', nestGetItem ],
  // tag
  [ 'get /tag/', tagGetIndex ],
])

export function parseRequests(body: MixModel['postIndexBody'])
{
  let result: ZZ = {}
  for (const [ _, item ] of body.entries())
  {
    if (!checkKeysExist(item, [ 'key', 'url' ])) continue
    const _method = item.method ? item.method.toLowerCase() : 'get'
    const _path = `${_method} ${item.url}`
    const _func = routeMap.get(_path)
    if (_func)
    {
      result[item.key] = {
        path: _path,
        func: _func,
        if: item.if ?? null,
        params: { ...(item.params ?? {}) },
      }
    }
    else
    {
      result[item.key] = null
    }
  }
  return result
}

export function parseParams(params: Record<string, unknown>, data: Record<string, unknown> = {}): Record<string, unknown>
{
  const pattern = /^\{\{(.*)\}\}$/
  for (const key of Object.keys(params))
  {
    const match = typeof params[key] === 'string' ? (params[key] as string).match(pattern) : null
    if (match)
    {
      const value = getValueDict(data, match[1] as string)
      if (typeof value === 'string' || typeof value === 'number' || typeof value === 'boolean')
      {
        params[key] = value
      }
      else if (value === null)
      {
        delete params[key]
      }
    }
  }
  return params
}

export function checkIf(condition: string, data: Record<string, unknown> = {}): boolean
{
  if (!condition) return false
  switch (condition.toLowerCase())
  {
    case 'true': return false
    case 'false': return true
    default: {
      const pattern = /^\{\{(.*)\}\}$/
      const match = condition.match(pattern)
      return getValueDict(data, match?.[1] ?? '') === null
    }
  }
}
