const { TEST_ACCESS_TOKEN } = Bun.env

const BASE_URL = 'http://localhost'

export function createRequest(path: string, init: RequestInit)
{
  const headers = new Headers(init.headers)
  if (!headers.get('Content-Type'))
  {
    headers.set('Content-Type', 'application/json')
  }
  if (TEST_ACCESS_TOKEN)
  {
    headers.set('Authorization', TEST_ACCESS_TOKEN)
  }
  return new Request(`${BASE_URL}${path}`, {
    ...init,
    headers,
  })
}
