export const LOG_IGNORE_PATHS_KEY = 'logging.ignore.paths'

let ignoredPaths: string[] = []

function normalizePaths(value: unknown): string[]
{
  if (!Array.isArray(value)) return []
  return Array.from(new Set(
    value.filter((item): item is string => typeof item === 'string')
      .map(item => item.trim())
      .filter(Boolean),
  ))
}

export function setLogIgnoredPaths(value: unknown): void
{
  ignoredPaths = normalizePaths(value)
}

function getPath(url: string): string
{
  try
  {
    return new URL(url).pathname
  }
  catch
  {
    return url.split('?')[0] || url
  }
}

function matchesPath(path: string, pattern: string): boolean
{
  if (pattern === '*') return true
  if (pattern.endsWith('*')) return path.startsWith(pattern.slice(0, -1))
  return path === pattern
}

export function isIgnoredLogRequest(request?: unknown): boolean
{
  if (
    !request
    || typeof request !== 'object'
    || !('url' in request)
    || typeof request.url !== 'string'
    || !request.url
    || ignoredPaths.length === 0
  ) return false
  const path = getPath(request.url)
  return ignoredPaths.some(pattern => matchesPath(path, pattern))
}
