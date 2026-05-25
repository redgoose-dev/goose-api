export interface ParamBase {
  table: string
  values?: ZZ & ParamsType
  debug?: boolean
  run?: boolean
}

export interface ParamGetIndex extends ParamBase {
  field?: string | string[]
  prefix?: string
  where?: string | string[]
  join?: string | string[]
  after?: string
  order?: string
  sort?: 'desc' | 'asc'
  page?: number
  size?: number
}

export interface ParamGetCount extends ParamBase {
  field?: string | string[]
  prefix?: string
  where?: string | string[]
  join?: string | string[]
  after?: string
}

export interface ParamGetData extends ParamBase {
  field?: string | string[]
  where?: string | string[]
  join?: string | string[]
}

export interface ParamAddData extends ParamBase {
  //
}

export type ReturnData = {
  data?: number | string | ZZ
  sql?: string
  values?: ZZ
}

export type ParamLimit = {
  page?: number
  size?: number
}
