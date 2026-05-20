import { Elysia } from 'elysia'
import getIndex from './get_index'

const routes = new Elysia({
  prefix: '/app',
})

routes.get('/', getIndex)

export default routes
