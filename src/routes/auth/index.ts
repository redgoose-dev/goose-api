import { Elysia } from 'elysia'
import { Auth } from './service'
import { AuthModel } from './model'
import { classifySrlCode } from '@/libs/service'
import { checkingToken } from '@/libs/verify'

const route = new Elysia({
  prefix: '/auth',
})


// 🌵 패스워드 타입의 프로바이더 로그인
route.post('/login/', async (ctx) => {
  const { request, body } = ctx
  const token = Auth.postLogin(body)
  return {
    message: 'Success login',
    data: {
      ...token,
    },
  }
}, {
  body: AuthModel.postLoginBody,
})


export default route
