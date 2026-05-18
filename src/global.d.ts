import type * as Elysia from 'elysia'

declare global {

  export type ZZ = Record<string|number, any>

  export type Context = Elysia.Context & {
    url: string
    body: ZZ
  }

}
