import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { BaseModel } from '@/libs/models'
import { ArticleModel } from './__model'

const route = new Elysia({
  prefix: '/article',
})

// 🌿 Index
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  const token = checkingToken(ctx, { usePublic: true })
  const data = await getIndex({
    query: ctx.query,
    token,
  })
  return {
    message: 'Complete get Article index.',
    data,
  }
}, {
  query: ArticleModel.getIndexQuery,
})

// 🌻 Detail
import { default as getItem } from './get-item'
route.get('/:srl/', async (ctx) => {
  const token = checkingToken(ctx, { usePublic: true })
  const data = await getItem({
    srl: ctx.params.srl,
    query: ctx.query,
    token,
  })
  return {
    message: 'Complete get Article.',
    data,
  }
}, {
  params: BaseModel.paramsSrl,
  query: ArticleModel.getItemQuery,
})

// 🌱 Create
import { default as putItem } from './put-item'
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await putItem()
  return {
    message: 'Complete add Article.',
    data,
  }
})

// 🌳 Patch
import { default as patchItem } from './patch-item'
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await patchItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update Article.'
}, {
  params: BaseModel.paramsSrl,
  body: ArticleModel.patchItemBody,
})

// 🍄 Delete
import { default as deleteItem } from './delete-item'
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  await deleteItem(ctx.params.srl)
  return 'Complete delete Article.'
}, {
  params: BaseModel.paramsSrl,
})

// 🌳 Change Nest
import { default as patchChangeNest } from './patch-change-nest'
route.patch('/:srl/change-nest/', async (ctx) => {
  checkingToken(ctx)
  await patchChangeNest({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update change.'
}, {
  params: BaseModel.paramsSrl,
  body: ArticleModel.patchChangeNestBody,
})

// 🌳 Update up count
import { default as patchUp } from './patch-up'
route.patch('/:srl/up/', async (ctx) => {
  checkingToken(ctx)
  await patchUp({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update up count.'
}, {
  params: BaseModel.paramsSrl,
  body: ArticleModel.patchUpBody,
})

export default route
