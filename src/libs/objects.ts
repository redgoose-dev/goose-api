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
