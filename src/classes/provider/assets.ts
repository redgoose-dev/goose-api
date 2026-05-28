// provider code
export const PROVIDER_CODE = {
  PASSWORD: 'password',
  DISCORD: 'discord',
  GOOGLE: 'google',
  GITHUB: 'github',
} as const
export type ProviderCode = typeof PROVIDER_CODE[keyof typeof PROVIDER_CODE]

// provider type
export const PROVIDER_TYPE = {
  PASSWORD: 'password',
  OAUTH: 'oauth',
} as const
export type ProviderType = typeof PROVIDER_TYPE[keyof typeof PROVIDER_TYPE]
