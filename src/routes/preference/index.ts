import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { Preference } from './service'
import { PreferenceModel } from './model'

const route = new Elysia({
  prefix: '/preference',
})

// 🌻 Detail
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const { service } = ctx.store as Store
  const data = await Preference.getItem(service)
  return {
    message: 'Complete get Preference.',
    data,
  }
})

// 🌳 Patch
route.patch('/', async (ctx) => {
  checkingToken(ctx)
  const { service } = ctx.store as Store
  await Preference.patchItem({
    service,
    body: ctx.body,
  })
  return 'Complete update Preference.'
}, {
  body: PreferenceModel.patchItemBody,
})

export default route
