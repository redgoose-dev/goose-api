import type * as Elysia from 'elysia'
import type { Logger } from 'logixlysia'
import type { Service as Service_ } from '@/classes/Service'

declare global {

  // Object type
  export type ZZ = Record<string|number, any>

  // Service
  export type Service = Service_

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
