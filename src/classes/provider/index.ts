import ProviderPassword from './Password'
import ProviderDiscord from './Discord'
import ProviderGoogle from './Google'
import ProviderGithub from './Github'
import { PROVIDER_CODE, type ProviderCode } from './assets'

export type ProviderClass = typeof ProviderPassword | typeof ProviderDiscord | typeof ProviderGoogle | typeof ProviderGithub
export type { ProviderCode }

const providerMap: Record<ProviderCode, ProviderClass> = {
  [PROVIDER_CODE.PASSWORD]: ProviderPassword,
  [PROVIDER_CODE.DISCORD]: ProviderDiscord,
  [PROVIDER_CODE.GOOGLE]: ProviderGoogle,
  [PROVIDER_CODE.GITHUB]: ProviderGithub,
}

/**
 * 코드에 맞는 프로바이더 클라스 가져오기
 * // TODO: 근데 안쓸수도 있다. ㅠㅠ 왜냐하면 명확하지 않은 출처를 낳기 때문이다.
 * @example
 * ```ts
 * const ProviderClass = getProviderClass(provider.data.code as ProviderCode)
 * ```
 */
export function getProviderClass(code: ProviderCode): ProviderClass
{
  const providerClass = providerMap[code as ProviderCode]
  if (!providerClass) throw new Error(`Invalid provider code: ${code}`)
  return providerClass
}
