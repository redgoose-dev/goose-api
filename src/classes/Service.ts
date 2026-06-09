import { exists } from 'node:fs/promises'
import { message } from '@/libs/cli'
import { setupDebug } from '@/libs/debug'
import { IS_DEV } from '@/libs/assets'
import pkg from '@/../package.json'
import originPreference from '@/../resource/preference.json'

const { SERVICE_NAME, PATH_DATA, DEBUG } = Bun.env

type ServiceData = {
  oAuth: Map<string, any>
}

class Service<T extends ZZ = ZZ> {

  public serviceName: string = SERVICE_NAME as string
  public version: string = pkg.version
  // 개발모드 여부
  public dev: boolean = IS_DEV
  // 디버그 출력 여부
  public useDebug: boolean = DEBUG?.toLowerCase() === 'true'
  // 빌드모드 여부
  public build: boolean = !!Bun.env.USE_BUILD
  // 설치되었는지 여부
  public installed: boolean = false
  // 환경설정
  public preference: ZZ = originPreference
  // 실행중에 사용되는 커스텀 데이터 공간
  public data: ServiceData = {
    oAuth: new Map(),
  }
  // preference 데이터 경로
  private pathPreference = `${PATH_DATA}/preference.json`

  constructor()
  {}

  async #checkInstall(): Promise<boolean>
  {
    const paths: string[] = [
      this.pathPreference,
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
    setupDebug({
      enabled: this.useDebug,
    })
    // checking install
    this.installed = await this.#checkInstall()
    if (!this.installed)
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
    const file = Bun.file(this.pathPreference, {
      type: 'application/json',
    })
    return (await file.json()) || undefined
  }

  async updatePreference(src: ZZ, change: boolean): Promise<void>
  {
    if (!(src && src.constructor == Object))
    {
      throw new Error('Not found update data.')
    }
    if (change)
    {
      this.preference = { ...src }
    }
    else
    {
      this.preference = {
        ...this.preference,
        ...src,
      }
    }
    const raw = JSON.stringify(this.preference, null, 2)
    await Bun.write(this.pathPreference, raw)
  }

}

export default Service
