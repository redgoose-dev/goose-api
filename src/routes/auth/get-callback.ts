import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'
import Provider from '@/classes/provider/Provider'
import { getProvider } from '@/classes/provider'
import { decodeUri, parseQueryString } from '@/libs/strings'
import { checkingToken } from '@/libs/verify'
import type { ProviderCode } from '@/classes/provider'
import type { AuthModel } from './__model'

type GetCallbackParams = {
  ctx: any
  code: string
  query: AuthModel['getCallbackQuery']
}

/**
 * OAuth 서비스에서 goose-api로 리다이렉트할때 처리하는 콜백
 */
export default async function getCallback({ ctx, code, query }: GetCallbackParams)
{
  type State = {
    access_token?: string
    redirect_uri?: string
    socket_id?: string
  }
  let state: State = decodeUri(query.state) as ZZ
  try
  {
    // if error then throw
    if (query.error) throw new ServiceError(query.error_description || query.error, { status: 401 })
    // get provider class
    const __provider__ = getProvider(code as ProviderCode)
    // get token
    const token = await __provider__.getToken(query.code)
    // DEV: 토큰을 얻어내는 과정을 생략하기 위한 임시코드
    // const token = {
    //   access: "MTM1MjM0ODU3MDE2NjIzMTIyMw.G6xy9nmbFA0JH6uJgluFGWr5tPa6Z0",
    //   expires: 604800,
    //   refresh: "0lWKPnKJc4dreY8HITxzcGLzI79L35",
    // }
    // get user
    const user = await __provider__.getUser(token.access)
    // get provider
    let providerSrl: number = NaN
    // 엑세스 토큰 검사
    if (state.access_token)
    {
      checkingToken(undefined, { accessToken: state.access_token })
    }
    // 프로바이더 데이터 가져오기
    const provider = db.getData({
      table: DB.TABLE.PROVIDER,
      where: [
        `AND code LIKE $code`,
        `AND user_id LIKE $userId`,
      ],
      values: {
        '$code': __provider__.code,
        '$userId': user.id,
      },
    })
    if (provider.data) providerSrl = provider.data.srl
    // 권한이 있고, 프로바이더가 없을때 새로운 프로바이더를 만든다.
    if (!providerSrl && state.access_token)
    {
      const provider = db.addData({
        table: DB.TABLE.PROVIDER,
        values: [
          { key: 'code', value: __provider__.code },
          { key: 'user_id', value: user.id },
          { key: 'user_name', value: user.name },
          { key: 'user_avatar', value: user.avatar },
          { key: 'user_email', value: user.email },
          { key: 'user_password', valueName: 'NULL' },
          { key: 'created_at', valueName: DB.DATE_TIME },
        ],
      })
      providerSrl = provider.data as number
    }
    // check provider srl
    if (!providerSrl)
    {
      throw new ServiceError('Not found provider.', { status: 401 })
    }
    // 새로운 토큰 만들기
    const _token = db.getData({
      table: DB.TABLE.TOKEN,
      where: `access LIKE $access`,
      values: { '$access': token.access },
    })
    if (_token.data)
    {
      db.editData({
        table: DB.TABLE.TOKEN,
        where: `srl = ${_token.data.srl}`,
        set: [
          'provider_srl = $provider_srl',
          'expires = $expires',
        ],
        values: {
          '$provider_srl': providerSrl,
          '$expires': token.expires,
        },
      })
    }
    else
    {
      db.addData({
        table: DB.TABLE.TOKEN,
        values: [
          { key: 'provider_srl', value: providerSrl },
          { key: 'access', value: token.access },
          { key: 'expires', value: token.expires },
          { key: 'refresh', value: token.refresh },
          { key: 'description', value: __provider__.description },
          { key: 'created_at', valueName: DB.DATE_TIME },
        ],
      })
    }
    // set result
    const data = {
      provider_srl: providerSrl,
      access: Provider.getPublicToken(token.access),
      expires: token.expires,
      refresh: token.refresh,
    }
    // response
    if (state.socket_id)
    {
      // 웹소켓 방식일때의 처리
      const { oAuth } = ctx.store.service.data
      const socket = oAuth.get(state.socket_id)
      if (socket?.ws)
      {
        socket.ws.send({
          mode: 'AUTH_COMPLETE',
          provider: data.provider_srl,
          access: data.access,
          expires: data.expires,
          refresh: data.refresh,
        })
        socket.ws.close()
      }
      return 'Complete auth. Please close this window.'
    }
    else if (state.redirect_uri)
    {
      const _qs = parseQueryString(data)
      // return `${state.redirect_uri}?${_qs}` // DEV
      return ctx.redirect(`${state.redirect_uri}?${_qs}`)
    }
    else
    {
      return `Complete OAuth ${__provider__.code}.`
    }
  }
  catch (_e: any)
  {
    const requestId = ctx.store.logger.getContext(ctx.request).requestId
    ctx.store.logger.error(ctx.request, _e.message, {
      errorMessage: _e.message,
    })
    if (state.socket_id)
    {
      // 웹소켓 방식일때의 처리
      const { oAuth } = ctx.store.service.data
      const data = oAuth.get(state.socket_id)
      if (data?.ws)
      {
        data.ws.send({
          mode: 'AUTH_ERROR',
          status_code: _e.status,
          request_id: requestId,
          message: _e.message,
        })
        data.ws.close()
      }
      return 'Failed auth. Please close this window.'
    }
    else if (state.redirect_uri)
    {
      const _qs = requestId ? { request_id: requestId } : {}
      const _query = parseQueryString(_qs)
      return ctx.redirect(_query ? `${state.redirect_uri}?${_query}` : state.redirect_uri)
    }
    else
    {
      return 'Failed auth.'
    }
  }
}
