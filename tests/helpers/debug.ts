
export function dconsole(title: string, data: any)
{
  console.group('-', title, '----')
  console.dir(data, { depth: null })
  console.log('------------------------------')
  console.groupEnd()
}

export async function getData(res: Response)
{
  const _text = await res.text()
  try
  {
    return JSON.parse(_text)
  }
  catch
  {
    return _text
  }
}
