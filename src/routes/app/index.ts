import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { classifySrlCode } from '@/libs/service'
import { BaseModel } from '@/libs/models'
import { App } from './service'
import { AppModel } from './model'

const route = new Elysia({
  prefix: '/app',
})

// 🌿 Index
route.get('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  // TODO: query.size - 기본값은 환경설정에서 값 가져오기
  // set query
  // if (query.page === undefined) ctx.query.page = 1
  // if (query.size === undefined) ctx.query.size = 33
  const data = await App.getIndex({
    ...ctx.query,
  })
  return {
    message: 'Complete get App index.',
    data,
  }
}, {
  query: AppModel.getIndexQuery,
})

// 🌻 Detail
route.get('/:srl/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  const data = await App.getItem({
    ...classifySrlCode(ctx.params.srl),
    ...ctx.query,
  })
  return {
    message: 'Complete get App.',
    data,
  }
}, {
  params: BaseModel.paramsSrlCode,
  query: AppModel.getItemQuery,
})

// 🌱 Create
route.put('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  const addedSrl = await App.putItem({ body: ctx.body })
  return {
    message: 'Complete add App.',
    data: addedSrl,
  }
}, {
  body: AppModel.putItemBody,
})

// 🌳 Patch
route.patch('/:srl/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  // patch data
  await App.patchItem({
    ...classifySrlCode(ctx.params.srl),
    body: ctx.body,
  })
  return 'Complete update App.'
}, {
  params: BaseModel.paramsSrlCode,
  body: AppModel.patchItemBody,
})

// 🍄 Delete
route.delete('/:srl/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  // delete data
  await App.deleteItem(classifySrlCode(ctx.params.srl))
  return 'Success delete App.'
}, {
  params: BaseModel.paramsSrlCode,
})

export default route
