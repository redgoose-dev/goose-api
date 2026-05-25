/**
 * 무슨 기능을 사용할지 가려주는 역할을 하는 모듈
 */

class MOD {

  public body: string[]

  constructor(raw?: string | null)
  {
	  this.body = raw ? raw.split(',') : []
  }

  get index(): string[]
  {
	  return this.body
  }

  get exist(): boolean
  {
	  return this.body.length > 0
  }

  public check(keyword: string): boolean
  {
	  return this.body.includes(keyword)
  }

}

export type { MOD }
export default MOD
