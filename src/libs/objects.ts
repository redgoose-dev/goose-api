/**
 * 객체에 값이 존재하는지 검사
 */
export function checkExistValueInObject(obj: ZZ, keys: string[]): boolean
{
  return keys.some(key => (key in obj) && obj[key] !== null && obj[key] !== undefined)
}

/**
 * value 값이 undefined 라면 key 삭제
 */
export function filteringObject(obj: ZZ)
{
  Object.entries(obj).forEach(([k,v]) => {
    if (v === undefined) delete obj[k]
  })
  return obj
}

/**
 * 배열을 객체로 변환 (code를 key로)
 */
export function arrayToObject(arr: ZZ[], keyName: string): ZZ
{
  return arr.reduce((acc: any, cur: any) => {
    acc[cur[keyName]] = cur
    return acc
  }, {})
}

/**
 * json 파싱
 */
export function parseJSON(src?: any): any
{
  try
  {
    if (src && typeof src === 'string') return JSON.parse(src)
    else if (src) return src
    else return null
  }
  catch (_e: any)
  {
    return null
  }
}
