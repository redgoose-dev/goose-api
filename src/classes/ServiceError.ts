type ServiceErrorOptions = {
  status?: number
  text?: string
  cause?: Error
  path?: string
  url?: URL
}

interface IServiceError {
  message: string // 서비스 메시지
  status: number // 상태코드
  errorMessage?: string // 디테일한 메시지
  error?: Error // 에러 객체
  path?: string // 오류가 발생한 경로
  url?: URL // 요청 URL
}

class ServiceError extends Error implements IServiceError {

  public status: number = 500
  declare public errorMessage?: string
  declare public error?: Error
  declare public path?: string
  declare public url?: URL

  constructor(message: string, op: ServiceErrorOptions = {})
  {
    super(message || 'Invalid Service Error')
    this.status = op.status ?? 500
    if (op.text !== undefined) this.errorMessage = op.text
    if (op.path !== undefined) this.path = op.path
    if (op.url !== undefined) this.url = op.url
  }

}

export type { ServiceError }
export default ServiceError
