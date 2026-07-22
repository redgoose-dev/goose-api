/**
 * 이미지 컨버팅 라이브러리
 * https://github.com/lovell/sharp
 */
export async function getSharp()
{
  return (await import('sharp')).default
}
