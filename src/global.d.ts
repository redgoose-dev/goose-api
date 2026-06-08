import type * as Elysia from 'elysia'
import type { Logger } from 'logixlysia'

declare global {

  // Object type
  export type ZZ = Record<string|number, any>

  // Module store in context
  export type Store = Elysia.Context['store'] & {
    beforeTime?: bigint
    service: Service_
    logger: Logger
  }

  // Module context
  export type Context = Elysia.Context & {
    store: Store
  }

  // Bun.env
  export type ENV = Bun.env & ZZ

}

export {}
