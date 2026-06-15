import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { BaseModel } from '@/libs/models'
import { JsonModel } from './__model'

const route = new Elysia({
  prefix: '/json',
})

// 🌿 Index
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const data = await getIndex({
    query: ctx.query,
  })
  return {
    message: 'Complete get JSON index.',
    data,
  }
}, {
  query: JsonModel.getIndexQuery,
})

// 🌻 Detail
import { default as getItem } from './get-item'
route.get('/:srl/', async (ctx) => {
  checkingToken(ctx)
  const data = await getItem({
    srl: ctx.params.srl,
    query: ctx.query,
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
import { default as putItem } from './put-item'
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await putItem({ body: ctx.body })
  return {
    message: 'Complete add JSON.',
    data,
  }
}, {
  body: JsonModel.putItemBody,
})

// 🌳 Patch
import { default as patchItem } from './patch-item'
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await patchItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update JSON.'
}, {
  params: BaseModel.paramsSrl,
  body: JsonModel.patchItemBody,
})

// 🍄 Delete
import { default as deleteItem } from './delete-item'
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await deleteItem(ctx.params.srl)
  return 'Complete delete JSON.'
}, {
  params: BaseModel.paramsSrl,
})

export default route
