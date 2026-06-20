import DB, { db } from '@/classes/DB'
import ServiceError from '@/classes/ServiceError'

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
type CheckingTokenOptions = {
  accessToken?: string // 우선으로 사용되는 엑세스 토큰
  checkExpires: boolean // 만료시간 검사여부
  usePublic?: boolean // 공개용 토큰 사용 여부
  useThrow?: boolean // 오류 비활성 여부
}
export type CheckinToken = {
  srl: number
  provider_srl: number
  access: string
  expires: number
  refresh: string
  description?: string
  created_at: string
  public: number
}
const defaultCheckingToken = {
  accessToken: undefined,
  checkExpires: true,
  usePublic: undefined,
  useThrow: true,
}
export function checkingToken(ctx: any, op: Partial<CheckingTokenOptions> = {}): CheckinToken
{
  const _op = { ...defaultCheckingToken, ...op }
  try
  {
    // get access token
    const _accessToken = _op.accessToken || getAccessToken(ctx)
    if (!_accessToken) throw new ServiceError('Token is invalid.', { status: 401 })
    // get token data
    const _token = db.getData({
      table: DB.TABLE.TOKEN,
      where: `access LIKE $accessToken`,
      values: { '$accessToken': `%${_accessToken}` },
    })
    if (!_token.data)
    {
      throw new ServiceError('Token data not found.', { status: 401 })
    }
    // 만료시간 검사하기
    if (_op.checkExpires)
    {
      if (_token.data.expires === 0)
      {
        throw new ServiceError('Expired token', { status: 401 })
      }
      if (!_op.usePublic)
      {
        // 토큰 유효시간(초) 가져오기
        const _expires: number = _token.data.expires || 0
        // 토큰 생성 시각 문자열을 timestamp(ms)로 변환
        const _createdAt: string = _token.data.created_at || ''
        const _createdTime = new Date(_createdAt.replace(' ', 'T')).getTime()
        // 생성 시각이 올바르지 않으면 인증 실패 처리
        if (!Number.isFinite(_createdTime))
        {
          throw new ServiceError('Token created time is invalid.', { status: 401 })
        }
        // 생성 시각 + 유효시간(초)로 만료 시각 계산
        const _expTime = _createdTime + (_expires * 1000)
        // 현재 시각이 만료 시각을 지났으면 만료된 토큰으로 처리
        if (Date.now() > _expTime)
        {
          throw new ServiceError('Expired token', { status: 401 })
        }
      }
    }
    return {
      ..._token.data,
      public: (_token.data.expires ?? 0) <= 0, // set public token flag
    }
  }
  catch (_e: any)
  {
    if (_op.useThrow)
    {
      throw _e
    }
    else
    {
      return null as any
    }
  }
}

function getAccessToken(ctx: any): string
{
  return ctx.request.token
    || ctx.query?.['_a']
    || ctx.headers?.authorization
    || ctx.request.headers.get('authorization')
    || ''
}
