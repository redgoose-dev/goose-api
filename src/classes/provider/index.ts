import ProviderPassword from './Password'
import ProviderDiscord from './Discord'
import ProviderGoogle from './Google'
import ProviderGithub from './Github'

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


export function getProvider(code: ProviderCode): any
{
  switch (code)
  {
    case PROVIDER_CODE.DISCORD:
      return new ProviderDiscord()
    case PROVIDER_CODE.GOOGLE:
      return new ProviderGoogle()
    case PROVIDER_CODE.GITHUB:
      return new ProviderGithub()
    case PROVIDER_CODE.PASSWORD:
      return new ProviderPassword()
    default:
      throw new Error(`Unknown provider code: ${code}`)
  }
}
