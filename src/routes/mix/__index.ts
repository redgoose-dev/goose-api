import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { BaseModel } from '@/libs/models'

const route = new Elysia({
  prefix: '/json',
})

