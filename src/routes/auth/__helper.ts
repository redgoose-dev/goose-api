export type ProviderData = {
  name?: string | null
  avatar?: string | null
  email?: string | null
  password?: string | null
  [key: string]: unknown
}
type Provider = {
  srl: number
  code: string
  uid: string
  data: string
  created_at: string
}

export function parseProviderData(value: string): ProviderData
{
  const data: unknown = JSON.parse(value)
  if (!data || typeof data !== 'object' || Array.isArray(data))
  {
    throw new Error('Invalid provider data.')
  }
  return data as ProviderData
}

// 존재하는 프로필만 반환하고 비밀 정보나 확장 필드는 노출하지 않는다.
export function getPublicProvider(provider: Provider)
{
  const data = parseProviderData(provider.data)
  return {
    srl: provider.srl,
    code: provider.code,
    uid: provider.uid,
    ...(typeof data.name === 'string' && { name: data.name }),
    ...(typeof data.avatar === 'string' && { avatar: data.avatar }),
    ...(typeof data.email === 'string' && { email: data.email }),
    created_at: provider.created_at,
  }
}
