import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { BaseModel } from '@/libs/models'
import { ChecklistModel } from './__model'

const route = new Elysia({
  prefix: '/checklist',
})

// 🌿 Index
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const data = await getIndex({
    query: ctx.query,
  })
  return {
    message: 'Complete get Checklist index.',
    data,
  }
}, {
  query: ChecklistModel.getIndexQuery,
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
    message: 'Complete get Checklist item.',
    data,
  }
}, {
  params: BaseModel.paramsSrl,
  query: ChecklistModel.getItemQuery,
})

// 🌱 Create
import { default as putItem } from './put-item'
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await putItem({
    body: ctx.body,
  })
  return {
    message: 'Complete add Checklist item.',
    data,
  }
}, {
  body: ChecklistModel.putItemBody,
})

// 🌳 Patch
import { default as patchItem } from './patch-item'
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await patchItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update Checklist item.'
}, {
  params: BaseModel.paramsSrl,
  body: ChecklistModel.patchItemBody,
})

// 🍄 Delete
import { default as deleteItem } from './delete-item'
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await deleteItem(ctx.params.srl)
  return 'Complete delete Checklist item.'
}, {
  params: BaseModel.paramsSrl,
})

export default route
