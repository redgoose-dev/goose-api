import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { Tag } from './service'
import { TagModel } from './model'

const route = new Elysia({
  prefix: '/tag',
})

// 🌿 Index
route.get('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  const data = await Tag.getIndex(ctx.query)
  return {
    message: 'Complete get Tag index.',
    data,
  }
}, {
  query: TagModel.getIndexQuery,
})

// 🌳 Patch
route.patch('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  await Tag.patchItem(ctx.body)
  return 'Complete update Tag.'
}, {
  body: TagModel.patchItemBody,
})

// 🍄 Delete
route.delete('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  await Tag.deleteItem(ctx.body)
  return 'Complete delete Tag.'
}, {
  body: TagModel.deleteItemBody,
})

export default route
