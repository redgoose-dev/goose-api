import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { PreferenceModel } from './__model'

const route = new Elysia({
  prefix: '/preference',
})

// 🌻 Detail
import { default as getIndex } from './get-index'
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const { service } = ctx.store as Store
  const data = await getIndex(service)
  return {
    message: 'Complete get Preference.',
    data,
  }
})

// 🌳 Patch
import { default as patchItem } from './patch-item'
route.patch('/', async (ctx) => {
  checkingToken(ctx)
  const { service } = ctx.store as Store
  await patchItem({
    service,
    body: ctx.body,
  })
  return 'Complete update Preference.'
}, {
  body: PreferenceModel.patchItemBody,
})

export default route
