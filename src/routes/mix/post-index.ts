import ServiceError from '@/classes/ServiceError'
import * as helper from './__helper'
import { classifySrlCode } from '@/libs/service'
import type { CheckinToken } from '@/libs/verify'
import type { MixModel } from './__model'

type PostIndexParams = {
  body: MixModel['postIndexBody']
  token: CheckinToken
  ctx: any
}

export default async function postIndex({ body, token, ctx }: PostIndexParams)
{
  try
  {
    const { logger, service } = ctx.store as Store
    let response: ZZ = {}

    // parse requests
    const requests = helper.parseRequests(body)

    // run requests
    const keys = Object.keys(requests)
    for (const key of keys)
    {
      try
      {
        const _req = requests[key]
        if (!(_req?.func && typeof _req.func === 'function'))
        {
          response[key] = null
          continue
        }
        // check 'if' condition
        if (helper.checkIf(_req.if, response)) continue
        // set params
        const _params: ZZ = helper.parseParams(_req.params ?? null, response)
        // run function
        let _res = await _req.func({
          ...(_params.srl ? classifySrlCode(_params.srl) : {}),
          query: _params,
          body: _params,
          token,
          service,
        })
        // set response
        if (_res)
        {
          if (typeof _res === 'string') _res = { message: _res }
          response[key] = {
            ok: true,
            ..._res,
          }
        }
        else
        {
          response[key] = { ok: false }
        }
      }
      catch(__e: any)
      {
        let _error: ZZ = {
          path: requests[key]?.path ?? undefined,
          stack: __e.stack,
        }
        let _res: ZZ = {
          ok: false,
        }
        if (__e instanceof ServiceError)
        {
          _error.status = __e.status
          _error.detail = __e.errorMessage
          _res.status = __e.status
          _res.message = __e.message
        }
        else
        {
          response[key] = { ok: false }
        }
        // set response
        response[key] = _res
        // call logger
        if (_res.status !== 204)
        {
          logger.error(ctx.request, __e.message, _error)
        }
      }
    }

    // get keys from response
    const responseKeys = Object.keys(response).join(',')

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
