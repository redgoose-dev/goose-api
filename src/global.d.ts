import type * as Elysia from 'elysia'
import type { Logger } from 'logixlysia'
import type Service from '@/classes/Service'

declare global {

  export type ZZ = Record<string|number, any>

  export type Context = Elysia.Context & {
    store: Elysia.Context['store'] & {
      service?: Service
      logger: Logger
    }
  } & ZZ

  export type ENV = Bun.env & ZZ

}

export {}
