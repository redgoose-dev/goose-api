import { Elysia } from 'elysia'
import { App } from './service'
import { AppModel } from './model'
import { classifySrlCode } from '@/libs/service'
import { checkingToken } from '@/libs/verify'

const route = new Elysia({
  prefix: '/app',
})

// 🌿 Index
route.get('/', async (ctx) => {
  const { request, query } = ctx
  // checking token
  const token = checkingToken(ctx)
  // TODO: query.size - 기본값은 환경설정에서 값 가져오기
  // set query
  if (query.page === undefined) query.page = 1
  if (query.size === undefined) query.size = 33
  const data = await App.getIndex({
    ...query,
  })
  return {
    message: 'Success get App index.',
    data,
  }
}, {
  query: AppModel.getIndexQuery,
})

// 🌻 Detail app
route.get('/:srl/', async (ctx) => {
  const { params, query } = ctx
  // TODO: 토큰 검사
  const data = await App.getItem({
    ...classifySrlCode(params.srl),
    ...query,
  })
  return {
    message: 'Success get App.',
    data,
  }
}, {
  params: AppModel.getItemParams,
  query: AppModel.getItemQuery,
})

// 🌱 Create app
route.put('/', async (ctx) => {
  const { request, body } = ctx
  // TODO: 토큰 검사
  const addedSrl = await App.putItem({ body })
  return {
    message: 'Success add App.',
    data: addedSrl,
  }
}, {
  body: AppModel.putItemBody,
})

// 🌳 Patch app data
route.patch('/:srl/', async (ctx) => {
  const { request, params, body } = ctx
  // TODO: 토큰 검사
  // patch data
  await App.patchItem({
    ...classifySrlCode(params.srl),
    body,
  })
  return 'Success update App.'
}, {
  params: AppModel.getItemParams,
  body: AppModel.patchItemBody,
})

// 🍄 Delete app
route.delete('/:srl/', async (ctx) => {
  const { request, params } = ctx
  // TODO: 토큰 검사
  // delete data
  await App.deleteItem(classifySrlCode(params.srl))
  return 'Success delete App.'
}, {
  params: AppModel.getItemParams,
})

export default route
