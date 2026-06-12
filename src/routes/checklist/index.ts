import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { Checklist } from './service'
import { BaseModel } from '@/libs/models'
import { ChecklistModel } from './model'

const route = new Elysia({
  prefix: '/checklist',
})

// 🌿 Index
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const data = await Checklist.getIndex({
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
route.get('/:srl/', async (ctx) => {
  checkingToken(ctx)
  const data = await Checklist.getItem({
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
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await Checklist.putItem({
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
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await Checklist.patchItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update Checklist item.'
}, {
  params: BaseModel.paramsSrl,
  body: ChecklistModel.patchItemBody,
})

// 🍄 Delete
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await Checklist.deleteItem(ctx.params.srl)
  return 'Complete delete Checklist item.'
}, {
  params: BaseModel.paramsSrl,
})

export default route
