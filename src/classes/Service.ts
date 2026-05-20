import { exists } from 'node:fs/promises'
import { message } from '@/libs/cli'
import pkg from '@/../package.json'
import preference from '@/../resource/preference.json'

const { SERVICE_NAME, PATH_DATA, NODE_ENV } = Bun.env

class Service<T extends ZZ> {

  public serviceName: string = SERVICE_NAME as string
  public version: string = pkg.version
  // 개발모드 여부
  public dev: boolean = NODE_ENV === 'development'
  // 빌드모드 여부
  public build: boolean = !!Bun.env.USE_BUILD
  // 설치되었는지 여부
  public installed: boolean = false
  // 환경설정
  public preference: ZZ = preference
  // 실행중에 사용되는 커스텀 데이터 공간
  public data = {} as T

  constructor()
  {}

  async #checkInstall(): Promise<boolean>
  {
    const paths: string[] = [
      `${PATH_DATA}/preference.json`,
      `${PATH_DATA}/db.sqlite`,
      `${PATH_DATA}/upload/origin`,
      `${PATH_DATA}/upload/cover`,
      `${PATH_DATA}/cache`,
      `${PATH_DATA}/logs`,
    ]
    for (const path of paths)
    {
      if (!(await exists(path))) return false
    }
    return true
  }

  async setup()
  {
    // checking install
    const installed = await this.#checkInstall()
    if (!installed)
    {
      message('error', 'Not Installed')
      process.exit(1)
    }
    // set preference
    const _preference = await this.loadPreference()
    if (_preference) this.preference = _preference
  }

  async loadPreference(): Promise<ZZ>
  {
    const file = Bun.file(`${PATH_DATA}/preference.json`, {
      type: 'application/json',
    })
    return (await file.json()) || undefined
  }

  async updatePreference(): Promise<void>
  {
    // TODO
  }

}

export default Service
