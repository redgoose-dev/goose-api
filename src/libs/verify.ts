import { db } from '@/classes/DB'

/**
 * 이메일 주소 검증하기
 */
export function verifyEmail(address: string): boolean
{
  if (!address) return false
  const emailRegex = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{1,}))$/;
  return emailRegex.test(String(address).toLowerCase())
}

/**
 * 아이디 검증하기
 */
export function verifyId(str: string): boolean
{
  return /^[a-zA-Z0-9_-]+$/.test(String(str))
}

/**
 * 토큰 검사하기
 */
export function checkingToken(ctx: any, accessToken?: string): ZZ
{
  const {  } = ctx
  // get access token
  const _token = accessToken || getAccessToken(ctx)
  // TODO: 여기서부터 작업하기
  // console.log('checkingToken()', _token)
  return {}
}

function getAccessToken(ctx: any): string
{
  console.log(ctx.query)
  return ctx.request.token
    || ctx.query?.['_a']
    || ctx.headers?.authorization
    || ctx.request.headers.get('authorization')
    || ''
}
