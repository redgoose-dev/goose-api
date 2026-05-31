import { Elysia } from 'elysia'
import { Auth } from './service'
import { BaseModel } from '@/libs/models'
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
route.post('/checkin/', async (ctx) => {
  const token = checkingToken(ctx)
  const data = await Auth.postCheckin(token)
  return {
    message: 'Complete checkin.',
    data,
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
  const data = await Auth.postReadyLogin({
    redirectUri: ctx.body.redirect_uri,
  })
  return {
    message: 'Complete get ready login data.',
    data,
  }
}, {
  body: AuthModel.postReadyLogin,
})

// 🌵 패스워드 타입의 프로바이더 로그인
route.post('/login/', async (ctx) => {
  const token = await Auth.postLogin(ctx.body)
  return {
    message: 'Complete login',
    data: { ...token },
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
route.get('/provider/', async (ctx) => {
  checkingToken(ctx)
  const data = await Auth.getProviderIndex(ctx.query)
  return {
    message: 'Complete get provider index.',
    data,
  }
}, {
  query: AuthModel.getProviderIndexQuery,
})

// 🌻 프로바이더 상세정보 조회
route.get('/provider/:srl/', async (ctx) => {
  const token = checkingToken(ctx)
  const data = await Auth.getProvider(ctx.params.srl, token)
  return {
    message: 'Complete get provider.',
    data,
  }
}, {
  params: BaseModel.paramsSrl,
})

// 🌱 패스워드 타입의 프로바이더 등록
route.put('/provider/', async (ctx) => {
  checkingToken(ctx)
  await Auth.putProvider(ctx.body)
  return 'Complete add provider.'
}, {
  body: AuthModel.putProviderBody,
})

// 🌳 패스워드 타입의 프로바이더 수정
route.patch('/provider/:srl/', async (ctx) => {
  checkingToken(ctx)
  await Auth.patchProvider(ctx.params.srl, ctx.body)
  return 'Complete update provider.'
}, {
  params: BaseModel.paramsSrl,
  body: AuthModel.patchProviderBody,
})

// 🍄 프로바이더 삭제
route.delete('/provider/:srl/', async (ctx) => {
  checkingToken(ctx)
  await Auth.deleteProvider(ctx.params.srl)
  return 'Complete delete provider.'
}, {
  params: BaseModel.paramsSrl,
})

// 🌿 공개용 토큰 목록 조회
route.get('/token/', async (ctx) => {
  checkingToken(ctx)
  const data = await Auth.getTokens(ctx.query)
  return {
    message: 'Complete get public tokens.',
    data,
  }
}, {
  query: AuthModel.getTokenQuery,
})

// 🌱 공개용 토큰 만들기
route.put('/token/', async (ctx) => {
  const token = checkingToken(ctx)
  const data = await Auth.putToken(ctx.body, token)
  return {
    message: 'Complete create public token.',
    data,
  }
}, {
  body: AuthModel.putTokenBody,
})

// 🌳 공개용 토큰 수정하기
route.patch('/token/:srl/', async (ctx) => {
  checkingToken(ctx)
  await Auth.patchToken(ctx.params.srl, ctx.body)
  return 'Complete update token.'
}, {
  params: BaseModel.paramsSrl,
  body: AuthModel.patchTokenBody,
})

// 🍄 공개용 토큰 만료시키기
route.delete('/token/:srl/', async (ctx) => {
  checkingToken(ctx)
  await Auth.revokeToken(ctx.params.srl)
  return 'The token has expired.'
}, {
  params: BaseModel.paramsSrl,
})

// 🍁 프로바이더 인증 웹소켓
// TODO

export default route
