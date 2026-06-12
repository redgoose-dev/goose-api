import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { File_ } from './service'
import { BaseModel } from '@/libs/models'
import { FileModel } from './model'

const route = new Elysia({
  prefix: '/file',
})

// 🌿 Index
route.get('/', async (ctx) => {
  // TODO
}, {})

// 🌻 Detail
route.get('/:srl/', async (ctx) => {
  // TODO
}, {
  params: BaseModel.paramsSrlCode,
})

// 🌱 Create
route.put('/', async (ctx) => {
  checkingToken(ctx)
  const data = await File_.putItem({
    body: ctx.body,
    service: (ctx.store as Store).service,
  })
  return {
    message: 'Complete add File.',
    data,
  }
}, {
  body: FileModel.putItemBody,
})

// 🌳 Patch
route.patch('/:srl/', async (ctx) => {
  checkingToken(ctx)
  // TODO
  return 'Complete update File.'
}, {
  params: BaseModel.paramsSrlCode,
  body: FileModel.patchItemBody,
})

// 🍄 Delete
route.delete('/:srl/', async (ctx) => {
  checkingToken(ctx)
  // TODO
}, {
  params: BaseModel.paramsSrlCode,
})

export default route
