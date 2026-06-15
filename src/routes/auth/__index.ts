import { Elysia } from 'elysia'
import { BaseModel } from '@/libs/models'
import { AuthModel } from './__model'
import { checkingToken } from '@/libs/verify'
import { IS_DEV } from '@/libs/assets'

const route = new Elysia({
  prefix: '/auth',
  websocket: {},
})

// 🌵 OAuth 인증요청으로 가기위한 경유지
// TODO: 어쩌면 안쓸지도 모르겠다. (웹소켓으로 주로 사용하는듯..)
import { default as getRedirect } from './get-redirect'
route.get('/redirect/:provider/', async (ctx) => {
  const url = await getRedirect({
    code: ctx.params.provider,
    query: ctx.query,
  })
  return ctx.redirect(url)
}, {
  params: BaseModel.paramsProvider,
  query: AuthModel.getRedirectQuery,
})

// 🌵 OAuth 에서 리다이렉트 콜백
import { default as getCallback } from './get-callback'
route.get('/callback/:provider/', async (ctx) => {
  return await getCallback({
    ctx,
    code: ctx.params.provider,
    query: ctx.query,
  })
}, {
  params: BaseModel.paramsProvider,
  query: AuthModel.getCallbackQuery,
})

// 🌵 인증 검사하기
import { default as postCheckin } from './post-checkin'
route.post('/checkin/', async (ctx) => {
  const token = checkingToken(ctx)
  const data = await postCheckin({
    token,
  })
  return {
    message: 'Complete checkin.',
    data,
  }
})

// 🌵 리프레시 토큰으로 엑세스 토큰 재발급받기
import { default as postRenew } from './post-renew'
route.post('/renew/', async (ctx) => {
  const token = checkingToken(ctx, {
    checkExpires: false,
  })
  const data = await postRenew({
    token,
    refreshToken: ctx.body.refresh,
  })
  return {
    message: 'Complete renew token.',
    data,
  }
}, {
  body: AuthModel.postRenewBody,
})

// 🌻 로그인 준비를 위한 재료 가져오기
import { default as postReadyLogin } from './post-ready-login'
route.post('/ready-login/', async (ctx) => {
  const data = await postReadyLogin({
    body: ctx.body,
  })
  return {
    message: 'Complete get ready login data.',
    data,
  }
}, {
  body: AuthModel.postReadyLogin,
})

// 🌵 패스워드 타입의 프로바이더 로그인
import { default as postLogin } from './post-login'
route.post('/login/', async (ctx) => {
  const data = await postLogin({
    body: ctx.body,
  })
  return {
    message: 'Complete login',
    data,
  }
}, {
  body: AuthModel.postLoginBody,
})

// 🌵 패스워드 타입의 프로바이더 로그아웃
import { default as postLogout } from './post-logout'
route.post('/logout/', async (ctx) => {
  const token = checkingToken(ctx)
  await postLogout({ token })
  return 'Complete logout'
})

// 🌿 프로바이더 목록 조회
import { default as getProviderIndex } from './get-provider-index'
route.get('/provider/', async (ctx) => {
  checkingToken(ctx)
  const data = await getProviderIndex({
    query: ctx.query,
  })
  return {
    message: 'Complete get provider index.',
    data,
  }
}, {
  query: AuthModel.getProviderIndexQuery,
})

// 🌻 프로바이더 상세정보 조회
import { default as getProviderItem } from './get-provider-item'
route.get('/provider/:srl/', async (ctx) => {
  const token = checkingToken(ctx)
  const data = await getProviderItem({
    srl: ctx.params.srl,
    token,
  })
  return {
    message: 'Complete get provider.',
    data,
  }
}, {
  params: BaseModel.paramsSrl,
})

// 🌱 패스워드 타입의 프로바이더 등록
import { default as putProviderItem } from './put-provider-item'
route.put('/provider/', async (ctx) => {
  checkingToken(ctx)
  await putProviderItem({
    body: ctx.body,
  })
  return 'Complete add provider.'
}, {
  body: AuthModel.putProviderBody,
})

// 🌳 패스워드 타입의 프로바이더 수정
import { default as patchProviderItem } from './patch-provider-item'
route.patch('/provider/:srl/', async (ctx) => {
  checkingToken(ctx)
  await patchProviderItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update provider.'
}, {
  params: BaseModel.paramsSrl,
  body: AuthModel.patchProviderBody,
})

// 🍄 프로바이더 삭제
import { default as deleteProviderItem } from './delete-provider-item'
route.delete('/provider/:srl/', async (ctx) => {
  checkingToken(ctx)
  await deleteProviderItem(ctx.params.srl)
  return 'Complete delete provider.'
}, {
  params: BaseModel.paramsSrl,
})

// 🌿 공개용 토큰 목록 조회
import { default as getTokenIndex } from './get-token-index'
route.get('/token/', async (ctx) => {
  checkingToken(ctx)
  const data = await getTokenIndex({
    query: ctx.query,
  })
  return {
    message: 'Complete get public token index.',
    data,
  }
}, {
  query: AuthModel.getTokenQuery,
})

// 🌱 공개용 토큰 만들기
import { default as putTokenItem } from './put-token-item'
route.put('/token/', async (ctx) => {
  const token = checkingToken(ctx)
  const data = await putTokenItem({
    body: ctx.body,
    token,
  })
  return {
    message: 'Complete create public Token.',
    data,
  }
}, {
  body: AuthModel.putTokenBody,
})

// 🌳 공개용 토큰 수정하기
import { default as patchTokenItem } from './patch-token-item'
route.patch('/token/:srl/', async (ctx) => {
  checkingToken(ctx)
  await patchTokenItem({
    srl: ctx.params.srl,
    body: ctx.body,
  })
  return 'Complete update token.'
}, {
  params: BaseModel.paramsSrl,
  body: AuthModel.patchTokenBody,
})

// 🍄 공개용 토큰 만료시키기
import { default as deleteTokenItem } from './delete-token-item'
route.delete('/token/:srl/', async (ctx) => {
  checkingToken(ctx)
  await deleteTokenItem(ctx.params.srl)
  return 'The token has expired.'
}, {
  params: BaseModel.paramsSrl,
})

// 🍁 프로바이더 인증 웹소켓
import * as socketAuthorize from './ws-authorize'
route.ws('/ws/authorize/', {
  body: AuthModel.wsAuthorizeMessageBody,
  open: (ws) => socketAuthorize.open(ws),
  message: (ws, body) => socketAuthorize.message(ws, body),
  close: (ws) => socketAuthorize.close(ws),
})

// for DEV
if (IS_DEV)
{
  route.get('/ws-test/', async () => {
    return new Response(Bun.file(`${import.meta.dir}/get-ws-test.html`), {
      headers: { 'Content-Type': 'text/html; charset=utf-8' },
    })
  })
}

export default route
