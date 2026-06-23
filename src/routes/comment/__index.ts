import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { BaseModel } from '@/libs/models'
import { CommentModel } from './__model'

const route = new Elysia({
  prefix: '/comment',
})

// 🌿 Index
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const data = await getIndex({
    query: ctx.query,
  })
  return {
    message: 'Complete get Comment index.',
    data,
  }
}, {
  query: CommentModel.getIndexQuery,
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
    message: '',
    data
  }
}, {
  params: BaseModel.paramsSrl,
  query: CommentModel.getItemQuery,
})

// 🌱 Create
import { default as putItem } from './put-item'
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await putItem({
    body: ctx.body,
  })
  return {
    message: 'Complete add Comment.',
    data,
  }
}, {
  body: CommentModel.putItemBody,
})

// 🌳 Patch
import { default as patchItem } from './patch-item'
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await patchItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
}, {
  params: BaseModel.paramsSrl,
  body: CommentModel.patchItemBody,
})

// 🍄 Delete
import { default as deleteItem } from './delete-item'
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await deleteItem(ctx.params.srl)
  return 'Complete delete Comment.'
}, {
  params: BaseModel.paramsSrl,
})

export default route
