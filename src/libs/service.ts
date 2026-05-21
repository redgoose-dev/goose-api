
export async function onRequest(ctx: any): Promise<void>
{
  // console.log('call onRequest()')
}

export function onResponse(_ctx: any): void
{
  // console.log('call onResponse()')
}

export function onError(_ctx: any): Response
{
  // console.error('call onError()')
  return new Response('error', {
    status: 500,
  })
}
