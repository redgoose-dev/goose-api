/**
 * prompt
 */
export async function prompt(message: string): Promise<string>
{
  process.stdout.write(`${message} `)
  for await (const line of console)
  {
    return line?.trim() || ''
  }
  return ''
}

/**
 * message
 */
type MessageType = 'start' | 'error' | 'exit' | 'run' | 'warning' | ''
export function message(type: MessageType, msg: string)
{
  switch (type)
  {
    case 'start':
      console.log('='.repeat(42))
      console.log('🪴', msg)
      console.log('='.repeat(42))
      break
    case 'warning':
      console.warn('⚠️', msg)
      break
    case 'error':
      console.error('❌', msg)
      break
    case 'exit':
      console.log('✅', msg)
      break
    case 'run':
      console.log('⌛', msg)
      break
    default:
      console.log(msg)
      break
  }
}
