
export function dconsole(title: string, data: any)
{
  console.group(title)
  console.dir(data, { depth: null })
  console.groupEnd()
}

export async function getData(res: Response)
{
  try
  {
    return await res.json()
  }
  catch (_e: any)
  {
    return await res.text()
  }
}
