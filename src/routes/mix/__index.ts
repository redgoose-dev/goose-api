import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { MixModel } from './__model'

const route = new Elysia({
  prefix: '/mix',
})

// 🌵 Main
import { default as postIndex } from './post-index'
route.post('/', async (ctx) => {
  const token = checkingToken(ctx, { usePublic: true })
  return await postIndex({
    body: ctx.body,
    token,
    ctx,
  })
}, {
  body: MixModel.postIndexBody,
})

export default route
