import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { classifySrlCode } from '@/libs/service'
import { BaseModel } from '@/libs/models'
import { AppModel } from './__model'

const route = new Elysia({
  prefix: '/app',
})

// 🌿 Index
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  // TODO: query.size - 기본값은 환경설정에서 값 가져오기
  // set query
  // if (query.page === undefined) ctx.query.page = 1
  // if (query.size === undefined) ctx.query.size = 33
  const data = await getIndex({
    query: ctx.query,
  })
  return {
    message: 'Complete get App index.',
    data,
  }
}, {
  query: AppModel.getIndexQuery,
})

// 🌻 Detail
import { default as getItem } from './get-item'
route.get('/:srl/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  const data = await getItem({
    ...classifySrlCode(ctx.params.srl),
    query: ctx.query,
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
import { default as putItem } from './put-item'
route.put('/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  const data = await putItem({ body: ctx.body })
  return {
    message: 'Complete add App.',
    data,
  }
}, {
  body: AppModel.putItemBody,
})

// 🌳 Patch
import { default as patchItem } from './patch-item'
route.patch('/:srl/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  // patch data
  await patchItem({
    ...classifySrlCode(ctx.params.srl),
    body: ctx.body,
  })
  return 'Complete update App.'
}, {
  params: BaseModel.paramsSrlCode,
  body: AppModel.patchItemBody,
})

// 🍄 Delete
import { default as deleteItem } from './delete-item'
route.delete('/:srl/', async (ctx) => {
  // checking token
  checkingToken(ctx)
  // delete data
  await deleteItem(classifySrlCode(ctx.params.srl))
  return 'Success delete App.'
}, {
  params: BaseModel.paramsSrlCode,
})

export default route
