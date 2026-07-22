import ServiceError from '@/classes/ServiceError'
import { getProvider } from '@/classes/provider'
import { encodeUri } from '@/libs/strings'
import { WS_TIMEOUT } from '@/libs/assets'

export async function open(ws: any)
{
  const { oAuth } = ws.data.store.service.data
  // set sessionId
  const session = ws.data.id
  // set timer for close
  const timer = setTimeout(() => {
    ws.send({ mode: 'AUTH_TIMEOUT', timeout: WS_TIMEOUT })
    ws.close()
  }, WS_TIMEOUT * 1000)
  // add session
  oAuth.set(session, {
    mode: 'open',
    ws, timer,
  })
  // send to client
  ws.send({
    mode: 'AUTH_OPEN',
    session,
    timeout: WS_TIMEOUT,
  })
}

export async function message(ws: any, data: ZZ)
{
  const { oAuth } = ws.data.store.service.data
  const { mode, provider, session, access_token } = data
  try
  {
    if (!oAuth.has(session)) return
    switch (mode)
    {
      case 'AUTH_START':
        const item = oAuth.get(session)
        oAuth.set(session, {
          ...item,
          mode: 'start',
          provider: provider,
        })
        const __provider__ = getProvider(provider)
        const url = __provider__.createAuthorizeUrl(encodeUri({
          socket_id: session,
          access_token: access_token,
        }))
        ws.send({
          mode: 'AUTH_LINK',
          url,
        })
        break
    }
  }
  catch (_e: any)
  {
    console.error(_e)
    if (oAuth.has(session)) oAuth.delete(session)
    throw new ServiceError('Failed auth with websocket.', {
      text: _e.message,
      cause: _e,
    })
  }
}

export async function close(ws: any)
{
  const { oAuth } = ws.data.store.service.data
  if (oAuth.has(ws.data.id))
  {
    const item = oAuth.get(ws.data.id)
    if (item.timer) clearTimeout(item.timer)
    oAuth.delete(ws.data.id)
  }
}
