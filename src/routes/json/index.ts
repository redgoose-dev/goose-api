import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { BaseModel } from '@/libs/models'
import { Json } from './service'
import { JsonModel } from './model'

const route = new Elysia({
  prefix: '/json',
})

// 🌿 Index
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const data = await Json.getIndex(ctx.query)
  return {
    message: 'Complete get JSON index.',
    data,
  }
}, {
  query: JsonModel.getIndexQuery,
})

// 🌻 Detail
route.get('/:srl/', async (ctx) => {
  checkingToken(ctx)
  const data = await Json.getItem({
    ...ctx.params,
    ...ctx.query,
  })
  return {
    message: 'Complete get JSON item.',
    data,
  }
}, {
  params: BaseModel.paramsSrl,
  query: JsonModel.getItemQuery,
})

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
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await Json.patchItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update JSON.'
}, {
  params: BaseModel.paramsSrl,
  body: JsonModel.patchItemBody,
})

// 🍄 Delete
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await Json.deleteItem(ctx.params.srl)
  return 'Complete delete JSON.'
}, {
  params: BaseModel.paramsSrl,
})

export default route
