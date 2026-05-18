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
