import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { classifySrlCode } from '@/libs/service'
import { BaseModel } from '@/libs/models'
import { Json } from './service'
import { JsonModel } from './model'

const route = new Elysia({
  prefix: '/json',
})

// 🌿 Index
route.get('/', async (ctx) => {}, {})

// 🌻 Detail
route.get('/:srl/', async (ctx) => {}, {})

// 🌱 Create
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const addedJsonSrl = await Json.putItem({ body: ctx.body })
  return {
    message: 'Complete add JSON.',
    data: addedJsonSrl,
  }
}, {
  body: JsonModel.putItemBody,
})

// 🌳 Patch
route.patch('/:srl/', async (ctx) => {}, {})

// 🍄 Delete
route.delete('/:srl/', async (ctx) => {}, {})

export default route
