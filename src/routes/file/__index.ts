import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { classifySrlCode } from '@/libs/service'
import { BaseModel } from '@/libs/models'
import { FileModel } from './__model'

const route = new Elysia({
  prefix: '/file',
})

// 🌿 Index
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  checkingToken(ctx, { usePublic: true })
  const data = await getIndex({
    query: ctx.query,
    service: (ctx.store as Store).service,
  })
  return {
    message: 'Complete get File index.',
    data,
  }
}, {
  query: FileModel.getIndexQuery,
})

// 🌻 Detail
import { default as getItem } from './get-item'
route.get('/:srl/', async (ctx) => {
  return await getItem({
    ...classifySrlCode(ctx.params.srl),
    query: ctx.query,
    ctx,
  })
}, {
  params: BaseModel.paramsSrlCode,
  query: FileModel.getItemQuery,
})

// 🌱 Create
import { default as putItem } from './put-item'
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await putItem({
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
import { default as patchItem } from './patch-item'
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  const data = await patchItem({
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
import { default as deleteItem } from './delete-item'
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await deleteItem(ctx.params.srl)
  return 'Complete delete File.'
}, {
  params: BaseModel.paramsSrl,
})

export default route
