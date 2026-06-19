import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { classifySrlCode } from '@/libs/service'
import { BaseModel } from '@/libs/models'
import { NestModel } from './__model'

const route = new Elysia({
  prefix: '/nest',
})

// 🌿 Index
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const data = await getIndex({
    query: ctx.query,
  })
  return {
    message: 'Complete get Nest index.',
    data,
  }
}, {
  query: NestModel.getIndexQuery,
})

// 🌻 Detail
import { default as getItem } from './get-item'
route.get('/:srl/', async (ctx) => {
  checkingToken(ctx)
  const data = await getItem({
    ...classifySrlCode(ctx.params.srl),
    query: ctx.query,
  })
  return {
    message: 'Complete get Nest item.',
    data,
  }
}, {
  params: BaseModel.paramsSrlCode,
  query: NestModel.getItemQuery,
})

// 🌱 Create
import { default as putItem } from './put-item'
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await putItem({
    body: ctx.body,
  })
  return {
    message: 'Complete add Nest.',
    data: undefined,
  }
}, {
  body: NestModel.putItemBody,
})

// 🌳 Patch
import { default as patchItem } from './patch-item'
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await patchItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update Nest.'
}, {
  params: BaseModel.paramsSrl,
  body: NestModel.patchItemBody,
})

// 🍄 Delete
import { default as deleteItem } from './delete-item'
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await deleteItem(ctx.params.srl)
  return 'Complete delete Nest.'
}, {
  params: BaseModel.paramsSrl,
})

export default route
