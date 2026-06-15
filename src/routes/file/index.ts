import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { File_ } from './service'
import { BaseModel } from '@/libs/models'
import { FileModel } from './model'

const route = new Elysia({
  prefix: '/file',
})

// 🌿 Index
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const data = await File_.getIndex({
    query: ctx.query,
  })
  return {
    message: 'Complete get File index.',
    data,
  }
}, {
  query: FileModel.getIndexQuery,
})

// 🌻 Detail
route.get('/:code/', async (ctx) => {
  checkingToken(ctx)
  return await File_.getItem({
    code: ctx.params.code,
    query: ctx.query,
    ctx,
  })
}, {
  params: BaseModel.paramsCode,
  query: FileModel.getItemQuery,
})

// 🌱 Create
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await File_.putItem({
    body: ctx.body,
    service: (ctx.store as Store).service,
  })
  return {
    message: 'Complete add File.',
    data,
  }
}, {
  body: FileModel.putItemBody,
})

// 🌳 Patch
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  const data = await File_.patchItem({
    srl: ctx.params.srl,
    body: ctx.body,
    service: (ctx.store as Store).service,
  })
  return {
    message: 'Complete update File.',
    data,
  }
}, {
  params: BaseModel.paramsSrl,
  body: FileModel.patchItemBody,
})

// 🍄 Delete
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await File_.deleteItem(ctx.params.srl)
  return 'Complete delete File.'
}, {
  params: BaseModel.paramsSrl,
})

export default route
