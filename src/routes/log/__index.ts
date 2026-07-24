import { Elysia } from 'elysia'
import { checkingToken } from '@/libs/verify'
import { getLogDatabase } from './__helper'
import { LogModel } from './__model'
import getIndex from './get-index'
import getItem from './get-item'
import getSummary from './get-summary'

const database = getLogDatabase()
const route = new Elysia({
  prefix: '/log',
})

// 🌿 Index
route.get('/', async (ctx) => {
  checkingToken(ctx)
  const data = await getIndex({
    query: ctx.query,
    database,
  })
  return {
    message: 'Complete get Log index.',
    data,
  }
}, {
  query: LogModel.getIndexQuery,
})

// 🌻 Detail
route.get('/:id/', async (ctx) => {
  checkingToken(ctx)
  const data = await getItem({
    id: ctx.params.id,
    database,
  })
  return {
    message: 'Complete get Log.',
    data,
  }
}, {
  params: LogModel.getItemParams,
})

// 🌻 Summary
route.get('/summary/', async (ctx) => {
  checkingToken(ctx)
  const data = await getSummary({
    query: ctx.query,
    database,
  })
  return {
    message: 'Complete get Log summary.',
    data,
  }
}, {
  query: LogModel.getSummaryQuery,
})

route.onStop(() => {
  database.close()
})

export default route
