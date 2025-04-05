from fastapi import APIRouter, Request, Query, Path, WebSocket, Form, Header
from src import __dev__
from src.libs.resource import Patterns
from . import __types__ as types

# setters
router = APIRouter()

# OAuth 인증요청으로 가기위한 경유지
@router.get('/redirect/{provider:str}/')
async def _get_redirect(
    provider: str = Path(..., pattern=Patterns.auth_provider),
    redirect_uri: str = Query(..., pattern=Patterns.url),
):
    from .get_redirect import get_redirect
    return await get_redirect({
        'provider': provider,
        'redirect_uri': redirect_uri,
    })

# OAuth 에서 리다이렉트 콜백
@router.get('/callback/{provider:str}/')
async def _get_callback(
    provider: str = Path(..., pattern=Patterns.auth_provider),
    code: str = Query(...),
    state: str = Query(...),
):
    from .get_callback import get_callback
    return await get_callback({
        'provider': provider,
        'code': code,
        'state': state,
    })

# 인증 검사하기
@router.post('/checking/')
async def _checking(req: Request):
    from .post_checking import post_checking
    return await post_checking(req=req)

# 리프레시 토큰으로 엑세스 토큰 재발급받기
@router.post('/renew/')
async def _renew(
    req: Request,
    provider: str = Form(..., pattern=Patterns.auth_provider),
    access_token: str = Header(..., alias='authorization'),
    refresh_token: str = Form('...', alias='refresh'),
):
    from .post_renew import post_renew
    return await post_renew({
        'provider': provider,
        'access_token': access_token,
        'refresh_token': refresh_token,
    }, req=req)

# 로그인 준비
@router.post('/ready-login/')
async def _post_ready_login(
    req: Request,
    redirect_uri: str = Form(..., pattern=Patterns.url),
):
    from .post_ready_login import post_ready_login
    return await post_ready_login({
        'redirect_uri': redirect_uri,
    }, req=req)

# 패스워드 타입의 프로바이더 로그인
@router.post('/login/')
async def _post_login(
    req: Request,
    user_id: str = Form(..., alias='id', pattern=Patterns.code),
    user_password: str = Form(..., alias='password'),
):
    from .post_login import post_login
    return await post_login({
        'user_id': user_id,
        'user_password': user_password,
    }, req=req)

# 패스워드 타입의 프로바이더 로그아웃
@router.post('/logout/')
async def _post_logout(req: Request):
    from .post_logout import post_logout
    return await post_logout(req=req)

# 프로바이더 목록
@router.post('/provider-index/')
async def _post_provider_index(
    req: Request,
    redirect_uri: str = Form(..., pattern=Patterns.url),
):
    from .post_provider_index import post_provider_index
    return await post_provider_index({
        'redirect_uri': redirect_uri,
    }, req=req)

# 패스워드 타입의 프로바이더 등록
@router.put('/provider/')
async def _put_provider(
    user_id: str = Form(..., alias='id', pattern=Patterns.code),
    user_name: str = Form(None, alias='name'),
    user_avatar: str = Form(None, alias='avatar', pattern=Patterns.url),
    user_email: str = Form(..., alias='email', pattern=Patterns.email),
    user_password: str = Form(..., alias='password'),
):
    from .put_provider import put_provider
    return await put_provider({
        'user_id': user_id,
        'user_name': user_name,
        'user_avatar': user_avatar,
        'user_email': user_email,
        'user_password': user_password,
    })

# 프로바이더 수정
@router.patch('/provider/{srl:int}/')
async def _patch_provider(
    req: Request,
    srl: int,
    user_id: str = Form(None, alias='id', pattern=Patterns.code),
    user_name: str = Form(None, alias='name'),
    user_avatar: str = Form(None, alias='avatar', pattern=Patterns.url),
    user_email: str = Form(None, alias='email', pattern=Patterns.email),
    user_password: str = Form(None, alias='password'),
):
    from .patch_provider import patch_provider
    return await patch_provider({
        'srl': srl,
        'user_id': user_id,
        'user_name': user_name,
        'user_avatar': user_avatar,
        'user_email': user_email,
        'user_password': user_password,
    }, req=req)

# 프로바이더 삭제
@router.delete('/provider/{srl:int}/')
async def _delete_provider(
    req: Request,
    srl: int
):
    from .delete_provider import delete_provider
    return await delete_provider({
        'srl': srl,
    }, req=req)

# 프로바이더 인증 웹소켓
@router.websocket('/ws/{socket_id:str}/')
async def _ws_index(ws: WebSocket, socket_id: str = Path(...)):
    from .ws_index import ws_index
    return await ws_index(ws, socket_id)

# for DEV
if __dev__:
    # 웹소켓 테스트
    @router.get('/test_websocket/')
    async def _test_websocket():
        from fastapi.responses import HTMLResponse
        from pathlib import Path
        path = Path(__file__).parent / 'get_test_websocket.html'
        with open(path, 'r', encoding='utf-8') as file: content = file.read()
        return HTMLResponse(content=content, status_code=200)
