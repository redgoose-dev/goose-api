// /**
//  * 객체에 값이 존재하는지 검사
//  * TODO: 이 함수는 안써도 될법하다. `src/routes/json/service.ts:check update data` 부분에서 함수없이 검사해놨다.
//  */
// export function checkExistValueInObject(obj: ZZ, keys: string[]): boolean
// {
//   return keys.some(key => (key in obj) && obj[key] !== null && obj[key] !== undefined)
// }

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

/**
 * 배열 두개를 비교하여 추가, 중복, 삭제 상황의 값들을 가져올 수 있다.
 */
type CompareResult<T> = {
  added: T[];
  duplicate: T[];
  removed: T[];
}
export function compareIndex<T>(a: T[], b: T[]): CompareResult<T>
{
  const nor = (value: T): T => (typeof value === 'string' ? value.trim() : value) as T
  const aNormalized = a.map(nor)
  const bNormalized = b.map(nor)
  return {
    added: b.filter((x) => x && !aNormalized.includes(nor(x))),
    duplicate: b.filter((x) => x && aNormalized.includes(nor(x))),
    removed: a.filter((x) => x && !bNormalized.includes(nor(x))),
  }
}

export function isObject(value: unknown): value is Record<string, unknown>
{
  return typeof value === 'object' && value !== null && !Array.isArray(value)
}
