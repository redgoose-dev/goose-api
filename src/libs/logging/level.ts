import type { LogLevel } from 'logixlysia'

/**
 * 예상 가능한 클라이언트 오류는 서버 오류로 집계하지 않는다.
 * 처리되지 않은 예외는 status가 없거나 5xx이므로 ERROR를 유지한다.
 */
export function normalizeLogLevel(level: LogLevel, status: unknown): LogLevel
{
  const statusCode = Number(status)
  if (
    level === 'ERROR'
    && Number.isInteger(statusCode)
    && statusCode >= 400
    && statusCode < 500
  ) return 'WARNING'
  return level
}
