import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { TagModel } from './__model'

const route = new Elysia({
  prefix: '/tag',
})

// 🌿 Index
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  const data = await getIndex({
    query: ctx.query,
    service: (ctx.store as Store).service,
  })
  return {
    message: 'Complete get Tag index.',
    data,
  }
}, {
  query: TagModel.getIndexQuery,
})

// 🌳 Patch
import { default as patchItem } from './patch-item'
route.patch('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  await patchItem({
    body: ctx.body,
  })
  return 'Complete update Tag.'
}, {
  body: TagModel.patchItemBody,
})

// 🍄 Delete
import { default as deleteItem } from './delete-item'
route.delete('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  await deleteItem({
    body: ctx.body,
  })
  return 'Complete delete Tag.'
}, {
  body: TagModel.deleteItemBody,
})

export default route
