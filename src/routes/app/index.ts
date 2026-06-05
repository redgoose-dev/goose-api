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
  const { request, query } = ctx
  // checking token
  checkingToken(ctx)
  // TODO: query.size - 기본값은 환경설정에서 값 가져오기
  // set query
  // if (query.page === undefined) query.page = 1
  // if (query.size === undefined) query.size = 33
  const data = await App.getIndex({
    ...query,
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
  const { params, query } = ctx
  // checking token
  checkingToken(ctx)
  const data = await App.getItem({
    ...classifySrlCode(params.srl),
    ...query,
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
  const { body } = ctx
  // checking token
  checkingToken(ctx)
  const addedSrl = await App.putItem({ body })
  return {
    message: 'Complete add App.',
    data: addedSrl,
  }
}, {
  body: AppModel.putItemBody,
})

// 🌳 Patch
route.patch('/:srl/', async (ctx) => {
  const { params, body } = ctx
  // checking token
  checkingToken(ctx)
  // patch data
  await App.patchItem({
    ...classifySrlCode(params.srl),
    body,
  })
  return 'Complete update App.'
}, {
  params: BaseModel.paramsSrlCode,
  body: AppModel.patchItemBody,
})

// 🍄 Delete
route.delete('/:srl/', async (ctx) => {
  const { params } = ctx
  // checking token
  checkingToken(ctx)
  // delete data
  await App.deleteItem(classifySrlCode(params.srl))
  return 'Success delete App.'
}, {
  params: BaseModel.paramsSrlCode,
})

export default route
