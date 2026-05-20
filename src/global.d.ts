import type * as Elysia from 'elysia'
import type Service from '@/classes/Service'

declare global {

  export type ZZ = Record<string|number, any>

  export type Context = Elysia.Context & {
    store: Elysia.Context['store'] & {
      service?: Service
    }
  } & ZZ

}

export {}
