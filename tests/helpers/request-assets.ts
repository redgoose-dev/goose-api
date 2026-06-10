const { TEST_ACCESS_TOKEN } = Bun.env

const BASE_URL = 'http://localhost'

type CreateRequestOptions = RequestInit & {
  query?: Record<string, string | number | boolean | undefined | null>
}
export function createRequest(path: string, init?: CreateRequestOptions)
{
  const headers = new Headers(init?.headers)
  if (!headers.get('Content-Type'))
  {
    headers.set('Content-Type', 'application/json')
  }
  if (TEST_ACCESS_TOKEN)
  {
    headers.set('Authorization', TEST_ACCESS_TOKEN)
  }
  const url = new URL(`${BASE_URL}${path}`)
  if (init?.query)
  {
    for (const [key, value] of Object.entries(init.query))
    {
      if (value !== undefined && value !== null)
      {
        url.searchParams.set(key, String(value))
      }
    }
  }
  return new Request(url.toString(), {
    ...init,
    headers,
  })
}
