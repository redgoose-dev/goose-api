export type ReturnData = {
  sql: string
  data: number | string | ZZ
}

export interface ParamBase {
  table: string
  values?: ZZ & ParamsType
  debug?: boolean
  run?: boolean
}

export interface ParamGetCount extends ParamBase {
  field?: string
  prefix?: string
  where?: string
  join?: string | string[]
  after?: string
}

export interface ParamAddData extends ParamBase {
  //
}
