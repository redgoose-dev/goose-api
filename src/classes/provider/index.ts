import ProviderPassword from './Password'
import ProviderDiscord from './Discord'
import ProviderGoogle from './Google'
import ProviderGithub from './Github'
import { PROVIDER_CODE, type ProviderCode } from './assets'

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
