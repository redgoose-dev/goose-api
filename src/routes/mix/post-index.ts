import ServiceError from '@/classes/ServiceError'
import * as helper from './__helper'
import { classifySrlCode } from '@/libs/service'
import { checkingToken } from '@/libs/verify'
import { getLogDatabase } from '@/routes/log/__helper'
import type { CheckinToken } from '@/libs/verify'
import type { MixModel } from './__model'

type PostIndexParams = {
  body: MixModel['postIndexBody']
  token: CheckinToken
  ctx: any
}

type MixResultState = 'success' | 'failed' | 'unsupported' | 'skipped'

type MixResult = {
  key: string
  path?: string
  state: MixResultState
  status?: number
  duration_ms?: number
}

export default async function postIndex({ body, token, ctx }: PostIndexParams)
{
  try
  {
    const { logger, service } = ctx.store as Store
    let response: ZZ = {}
    let successCount = 0
    let failureCount = 0
    let skippedCount = 0
    let hasServerFailure = false
    const mixResults: MixResult[] = []

    // parse requests
    const requests = helper.parseRequests(body)

    // run requests
    const keys = Object.keys(requests)
    const totalCount = keys.length
    const mixBeforeTime = process.hrtime.bigint()
    for (const key of keys)
    {
      const _req = requests[key]
      const requestBeforeTime = process.hrtime.bigint()
      try
      {
        if (!(_req?.func && typeof _req.func === 'function'))
        {
          response[key] = null
          failureCount++
          mixResults.push({
            key,
            path: _req?.path,
            state: 'unsupported',
            status: 404,
            duration_ms: getDuration(requestBeforeTime),
          })
          logMix(logger, ctx.request, 'WARNING', 'Mix request is not available.', {
            mix_key: key,
            mix_path: _req?.path,
            mix_total: totalCount,
          }, requestBeforeTime, 404)
          continue
        }
        // check 'if' condition
        if (!helper.checkIf(_req.if, response))
        {
          skippedCount++
          mixResults.push({
            key,
            path: _req.path,
            state: 'skipped',
          })
          continue
        }
        if (helper.PRIVATE_ROUTE_PATHS.has(_req.path)) checkingToken(ctx)
        // set params
        const _params: ZZ = helper.parseParams(_req.params ?? null, response)
        // run function
        let _res = await _req.func({
          ...(_params.srl ? classifySrlCode(_params.srl) : {}),
          query: _params,
          body: _params,
          token,
          service,
          database: getLogDatabase(),
        })
        const status = getResponseStatus(_res)
        // set response
        if (_res)
        {
          if (typeof _res === 'string') _res = { message: _res }
          response[key] = { ..._res }
        }
        else
        {
          response[key] = {}
        }
        if (status >= 400)
        {
          failureCount++
          if (status >= 500) hasServerFailure = true
          mixResults.push({
            key,
            path: _req.path,
            state: 'failed',
            status,
            duration_ms: getDuration(requestBeforeTime),
          })
          logMix(logger, ctx.request, status >= 500 ? 'ERROR' : 'WARNING', 'Mix request failed.', {
            mix_key: key,
            mix_path: _req.path,
            mix_total: totalCount,
          }, requestBeforeTime, status)
        }
        else
        {
          successCount++
          mixResults.push({
            key,
            path: _req.path,
            state: 'success',
            status,
            duration_ms: getDuration(requestBeforeTime),
          })
        }
      }
      catch(__e: any)
      {
        let _res: ZZ = {}
        const status = getErrorStatus(__e)
        if (__e instanceof ServiceError)
        {
          _res.status = __e.status
          _res.message = __e.message
        }
        else
        {
          response[key] = {}
        }
        // set response
        response[key] = _res
        if (status === 204)
        {
          successCount++
          mixResults.push({
            key,
            path: _req?.path,
            state: 'success',
            status,
            duration_ms: getDuration(requestBeforeTime),
          })
        }
        else
        {
          failureCount++
          if (status >= 500) hasServerFailure = true
          mixResults.push({
            key,
            path: _req?.path,
            state: 'failed',
            status,
            duration_ms: getDuration(requestBeforeTime),
          })
          logMix(logger, ctx.request, status >= 500 ? 'ERROR' : 'WARNING', 'Mix request failed.', {
            mix_key: key,
            mix_path: _req?.path,
            mix_total: totalCount,
            mix_error_detail: __e instanceof ServiceError ? __e.errorMessage : undefined,
          }, requestBeforeTime, status, __e)
        }
      }
    }

    // get keys from response
    const responseKeys = Object.keys(response).join(',')
    const summaryStatus = failureCount > 0
      ? (hasServerFailure ? 500 : 400)
      : 200
    const summaryLevel = failureCount > 0
      ? (hasServerFailure ? 'ERROR' : 'WARNING')
      : 'INFO'
    logMix(logger, ctx.request, summaryLevel, 'Mix request completed.', {
      mix_total: totalCount,
      mix_success: successCount,
      mix_failed: failureCount,
      mix_skipped: skippedCount,
      mix_keys: responseKeys,
      mix_results: mixResults,
    }, mixBeforeTime, summaryStatus)

    return {
      __message: `Complete mixing "${responseKeys}" requests.`,
      ...response,
    }
  }
  catch (_e: any)
  {
    throw new ServiceError('Failed to post mix.', {
      status: _e.status,
      text: _e.message,
      cause: _e,
    })
  }
}

function getResponseStatus(response: unknown): number
{
  if (response && typeof response === 'object' && 'status' in response)
  {
    const status = Number(response.status)
    if (Number.isInteger(status) && status >= 100 && status <= 599) return status
  }
  return 200
}

function getErrorStatus(error: unknown): number
{
  if (error && typeof error === 'object' && 'status' in error)
  {
    const status = Number(error.status)
    if (Number.isInteger(status) && status >= 100 && status <= 599) return status
  }
  return 500
}

function getDuration(beforeTime: bigint): number
{
  const duration = Number(process.hrtime.bigint() - beforeTime) / 1_000_000
  return Number(duration.toFixed(3))
}

function logMix(
  logger: Store['logger'],
  request: Request,
  level: Parameters<Store['logger']['log']>[0],
  message: string,
  context: ZZ,
  beforeTime: bigint,
  status: number,
  error?: unknown,
): void
{
  logger.log(level, request, {
    message,
    status,
    context: Object.fromEntries(Object.entries(context).filter(([, value ]) => value != null)),
    error,
  }, { beforeTime })
}
