import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { BaseModel } from '@/libs/models'
import { CategoryModel } from './__model'

const route = new Elysia({
  prefix: '/category',
})

// 🌿 Index
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const data = await getIndex({
    query: ctx.query,
    service: (ctx.store as Store).service,
  })
  return {
    message: 'Complete get Category index.',
    data,
  }
}, {
  query: CategoryModel.getIndexQuery,
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
    message: 'Complete get Category item.',
    data,
  }
}, {
  params: BaseModel.paramsSrl,
  query: CategoryModel.getItemQuery,
})

// 🌱 Create
import { default as putItem } from './put-item'
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await putItem({ body: ctx.body })
  return {
    message: 'Complete add Category.',
    data,
  }
}, {
  body: CategoryModel.putItemBody,
})

// 🌳 Patch
import { default as patchItem } from './patch-item'
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await patchItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update Category.'
}, {
  params: BaseModel.paramsSrl,
  body: CategoryModel.patchItemBody,
})

// 🌳 Change order
import { default as patchChangeOrder } from './patch-change-order'
route.patch('/change-order/', async (ctx) => {
  checkingToken(ctx)
  await patchChangeOrder({
    body: ctx.body,
  })
  return 'Complete change order Category.'
}, {
  body: CategoryModel.patchChangeOrderBody,
})

// 🍄 Delete
import { default as deleteItem } from './delete-item'
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await deleteItem(ctx.params.srl)
  return 'Complete delete Category.'
}, {
  params: BaseModel.paramsSrl,
})

export default route
