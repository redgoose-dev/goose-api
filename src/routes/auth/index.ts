import { Elysia } from 'elysia'
import { Auth } from './service'
import { AuthModel } from './model'
import { checkingToken } from '@/libs/verify'

const route = new Elysia({
  prefix: '/auth',
})

// 🌵 인증 검사하기
// TODO: 레거시에서 주소가 `/auth/checking/`로 되어있다.
route.post('/checkin/', async (ctx) => {
  const token = checkingToken(ctx)
  return {
    message: 'Success checkin.',
    data: {
      ...Auth.postCheckin(token),
    },
  }
})

// 🌵 리프레시 토큰으로 엑세스 토큰 재발급받기
route.post('/renew/', async (ctx) => {
  const token = checkingToken(ctx, {
    checkExpires: false,
  })
  return {
    message: 'Success renew token.',
    data: await Auth.postRenew(token, ctx.body.refresh),
  }
}, {
  body: AuthModel.postRenewBody,
})

// 🌻 로그인 준비를 위한 재료 가져오기
route.post('/ready-login/', async (ctx) => {
  return {
    message: 'Success get ready login data.',
    data: Auth.postReadyLogin({
      redirectUri: ctx.body.redirect_uri,
    }),
  }
}, {
  body: AuthModel.postReadyLogin,
})

// 🌵 패스워드 타입의 프로바이더 로그인
route.post('/login/', async (ctx) => {
  const { request, body } = ctx
  const token = await Auth.postLogin(body)
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
