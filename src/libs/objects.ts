/**
 * 객체에 값이 존재하는지 검사
 */
export function checkExistValueInObject(obj: ZZ, keys: string[]): boolean
{
  return keys.some(key => (key in obj) && obj[key] !== null && obj[key] !== undefined)
}
