import { Elysia } from 'elysia'
import { Auth } from './service'
import { AuthModel } from './model'
import { checkingToken } from '@/libs/verify'

const route = new Elysia({
  prefix: '/auth',
})

// 🌵 OAuth 인증요청으로 가기위한 경유지
// TODO

// 🌵 OAuth 에서 리다이렉트 콜백
// TODO

// 🌵 인증 검사하기
// TODO: 레거시에서 주소가 `/auth/checking/`로 되어있다.
route.post('/checkin/', async (ctx) => {
  const token = checkingToken(ctx)
  return {
    message: 'Complete checkin.',
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
    message: 'Complete renew token.',
    data: await Auth.postRenew(token, ctx.body.refresh),
  }
}, {
  body: AuthModel.postRenewBody,
})

// 🌻 로그인 준비를 위한 재료 가져오기
route.post('/ready-login/', async (ctx) => {
  return {
    message: 'Complete get ready login data.',
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
    message: 'Complete login',
    data: {
      ...token,
    },
  }
}, {
  body: AuthModel.postLoginBody,
})

// 🌵 패스워드 타입의 프로바이더 로그아웃
route.post('/logout/', async (ctx) => {
  const token = checkingToken(ctx)
  await Auth.postLogout(token.srl)
  return 'Complete logout.'
})

// 🌿 프로바이더 목록 조회
// TODO

// 🌻 프로바이더 상세정보 조회
// TODO

// 🌱 패스워드 타입의 프로바이더 등록
route.put('/provider/', async (ctx) => {
  checkingToken(ctx)
  Auth.putProvider(ctx.body)
  return 'Complete add provider.'
}, {
  body: AuthModel.putProviderBody,
})

// 🌳 패스워드 타입의 프로바이더 수정
route.patch('/provider/:srl/', async (ctx) => {
  checkingToken(ctx)
  Auth.patchProvider(ctx.params.srl, ctx.body)
  return 'Complete update provider.'
}, {
  params: AuthModel.patchProviderParams,
  body: AuthModel.patchProviderBody,
})

// 🍄 프로바이더 삭제
route.delete('/provider/:srl/', async (ctx) => {
  checkingToken(ctx)
  Auth.deleteProvider(ctx.params.srl)
  return 'Complete delete provider.'
}, {
  params: AuthModel.deleteProviderParams,
})

// 🌿 공개용 토큰 목록 조회
// TODO

// 🌱 공개용 토큰 만들기
// TODO

// 🌳 공개용 토큰 수정하기
// TODO

// 🍄 공개용 토큰 만료시키기
// TODO

// 🍁 프로바이더 인증 웹소켓
// TODO

export default route
