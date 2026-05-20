import { colorText } from './strings'

/**
 * 서버 오픈 메시지 출력
 */
export function openServer(_app: Pick<Context, 'server'|'store'>): void
{
  const { server, store } = _app
  if (!server) return

  const { port, hostname, development } = server
  const { service } = store
  const serviceName = service.serviceName ?? 'Service'
  const assets = {
    cyan: '\x1b[36m',
    yellow: '\x1b[33m',
    reset: '\x1b[0m',
    line: Array(46).fill('=').join(''),
    development: 'Development',
    production: 'Production',
    intent: Array(2).fill(' ').join(''),
  }
  // set mode text
  let mode
  if (development)
  {
    mode = colorText(`[${assets.development}]`, 'yellow')
  }
  else
  {
    mode = colorText(`[${assets.production}]`, 'blue')
  }
  // print server info
  console.log(assets.line)
  console.log(`${assets.intent}${colorText(serviceName, 'green')} ${mode}`)
  console.log(`${assets.intent}➜ Local: ${colorText(`${hostname}:${port}`, 'cyan')}`)
  console.log(assets.line)
}
