import { colorText, dateFormat } from '@/libs/strings'

/**
 * 사용법
 *
 * 1. 서비스 시작 지점에서 한번만 초기화한다.
 * ```ts
 * import { setupDebug } from '@/libs/debug'
 *
 * setupDebug({
 *   enabled: service.dev,
 *   depth: 4,
 * })
 * ```
 *
 * 2. 필요한 곳에서 `debug()` 를 호출한다.
 * ```ts
 * import debug from '@/libs/debug'
 *
 * debug('service.setup:start')
 * debug('auth.login', {
 *   userId,
 *   provider,
 * })
 * ```
 *
 * 3. 더 깊게 보고싶은 객체는 `depth` 옵션을 넘긴다.
 * ```ts
 * debug('article.detail', article, {
 *   depth: 6,
 * })
 * ```
 *
 * 4. Error 객체를 넘기면 message, stack 등을 보기 좋게 출력한다.
 * ```ts
 * try
 * {
 *   // ...
 * }
 * catch (error)
 * {
 *   debug('article.remove:error', error)
 * }
 * ```
 *
 * 참고
 * - 개발모드에서만 활성화하도록 `setupDebug({ enabled })` 로 제어한다.
 * - 현재는 콘솔 출력 전용이다.
 * - `label` 은 `domain.action:state` 형태로 통일하면 검색이 쉽다.
 */

type DebugConfig = {
  enabled?: boolean
  depth?: number
}
type DebugPrintOptions = {
  depth?: number
  stackIndex?: number
}
type DebugState = {
  enabled: boolean
  depth: number
}

const state: DebugState = {
  enabled: false,
  depth: 4,
}

function getTimestamp(): string
{
  return dateFormat(new Date(), '{yyyy}-{MM}-{dd} {hh}:{mm}:{ss}')
}

function normalizeStackLine(line?: string): string
{
  if (!line) return ''
  return line
    .replace(/^at\s+/, '')
    .replace(process.cwd(), '.')
    .replace('file://', '')
    .trim()
}

function getCaller(stackIndex: number = 3): string
{
  const lines = new Error().stack?.split('\n').map(v => v.trim()).filter(Boolean) || []
  return normalizeStackLine(lines[stackIndex] || lines.at(-1))
}

function normalizePayload(payload: unknown): unknown
{
  if (payload instanceof Error)
  {
    return {
      name: payload.name,
      message: payload.message,
      stack: payload.stack,
      cause: payload.cause,
    }
  }
  return payload
}

export function setupDebug(op: DebugConfig = {}): void
{
  if (op.enabled !== undefined) state.enabled = op.enabled
  if (op.depth !== undefined) state.depth = op.depth
}

export default function debug(label: string, payload?: unknown, op: DebugPrintOptions = {}): void
{
  if (!state.enabled) return

  const caller = getCaller(op.stackIndex ?? 3)
  const head = [
    '🐛',
    colorText('[DEBUG]', 'magenta'),
    colorText(getTimestamp(), 'cyan'),
    colorText(label, 'yellow'),
    caller ? colorText(`@ ${caller}`, 'blue') : '',
  ].filter(Boolean).join(' ')

  if (payload === undefined)
  {
    console.log(head)
    return
  }

  const normalized = normalizePayload(payload)
  const isPrimitive = [ 'string', 'number', 'boolean', 'bigint' ].includes(typeof normalized) || normalized == null
  if (isPrimitive)
  {
    console.log(head, normalized)
    return
  }

  if (typeof console.groupCollapsed === 'function')
  {
    console.groupCollapsed(head)
    console.dir(normalized, {
      depth: op.depth ?? state.depth,
      colors: true,
    })
    console.groupEnd()
    return
  }

  console.log(head)
  console.dir(normalized, {
    depth: op.depth ?? state.depth,
    colors: true,
  })
}

export type {
  DebugConfig,
  DebugPrintOptions,
}

