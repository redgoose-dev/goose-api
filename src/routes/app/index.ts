import { Elysia, t } from 'elysia'
import { App } from './service'
import { AppModel } from './model'
import { classifySrlCode } from '@/libs/service'

const route = new Elysia({
  prefix: '/app',
})

// 🌿 Index
route.get('/', async (ctx) => {
  const { request, query } = ctx
  // TODO: 토큰 검사
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
  const addedSrl = await App.putItem({ request, body })
  return {
    message: 'Success add App.',
    data: addedSrl,
  }
}, {
  body: AppModel.putItemBody,
})

// 🌳 Update app
route.patch('/:srl/', async (ctx) => {
  // TODO
}, {
  // params,
  // body,
})

// 🍄 Delete app
route.delete('/:srl/', async (ctx) => {
  // TODO
}, {
  // params,
})

export default route
