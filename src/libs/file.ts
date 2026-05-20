/**
 * exist file
 */
export async function existFile(path: string): Promise<boolean>
{
  const file = Bun.file(path)
  return await file.exists()
}
