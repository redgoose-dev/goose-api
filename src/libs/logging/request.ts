export type RequestTracking = {
  referer?: string
  origin?: string
  client_ip?: string
  user_agent?: string
}

type RequestIPServer = {
  requestIP?: (request: Request) => {
    address?: string
  } | null
}

const trackingByRequestId = new Map<string, RequestTracking>()
const requestIds = new WeakMap<Request, string>()

function getHeader(request: Request, name: string): string | undefined
{
  const value = request.headers.get(name)?.trim()
  return value || undefined
}

function getRequestId(context: unknown): string | undefined
{
  if (!context || typeof context !== 'object') return undefined
  const source = context as Record<string, unknown>
  const value = source.requestId ?? source.request_id
  return typeof value === 'string' && value ? value : undefined
}

function getClientIP(request: Request, server?: RequestIPServer | null): string | undefined
{
  const forwarded = getHeader(request, 'x-forwarded-for')
  if (forwarded)
  {
    const clientIP = forwarded.split(',')[0]?.trim()
    if (clientIP) return clientIP
  }

  const realIP = getHeader(request, 'x-real-ip')
  if (realIP) return realIP

  const address = server?.requestIP?.(request)?.address?.trim()
  return address || undefined
}

export function registerRequestTracking(
  request: Request,
  context: unknown,
  server?: RequestIPServer | null,
): void
{
  const requestId = getRequestId(context)
  if (!requestId) return

  trackingByRequestId.set(requestId, {
    referer: getHeader(request, 'referer'),
    origin: getHeader(request, 'origin'),
    client_ip: getClientIP(request, server),
    user_agent: getHeader(request, 'user-agent'),
  })
  requestIds.set(request, requestId)
}

export function getRequestTracking(context: unknown): RequestTracking | undefined
{
  const requestId = getRequestId(context)
  return requestId ? trackingByRequestId.get(requestId) : undefined
}

export function clearRequestTracking(request: Request): void
{
  const requestId = requestIds.get(request)
  if (!requestId) return
  trackingByRequestId.delete(requestId)
  requestIds.delete(request)
}
