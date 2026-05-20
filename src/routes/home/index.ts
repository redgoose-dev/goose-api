import { Elysia } from 'elysia'
import getIndex from './get_index'

const routes = new Elysia({
  prefix: '/',
})

routes.get('/', getIndex)

export default routes
