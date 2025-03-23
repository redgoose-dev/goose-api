from fastapi import APIRouter, Query, Path, WebSocket, Form, Header
from src import __dev__
from src.libs.resource import Patterns
from . import __types__ as types

# setters
router = APIRouter()

# 프로바이더 목록
@router.get('/')
async def _get_index(
    fields: str = Query(None, pattern=Patterns.fields),
):
    from .get_index import get_index
    return await get_index(types.GetIndex(
        fields = fields,
    ))

# OAuth 인증요청으로 가기위한 경유지
@router.get('/redirect/{provider:str}/')
async def _get_redirect(
    provider: str = Path(..., pattern=Patterns.auth_provider),
    redirect_uri: str = Query(..., pattern=Patterns.url),
):
    from .get_redirect import get_redirect
    return await get_redirect(types.GetRedirect(
        provider = provider,
        redirect_uri = redirect_uri,
    ))

# OAuth 에서 리다이렉트 콜백
@router.get('/callback/{provider:str}/')
async def _get_callback(
    provider: str = Path(..., pattern=Patterns.auth_provider),
    code: str = Query(...),
    state: str = Query(...),
):
    from .get_callback import get_callback
    return await get_callback(types.GetCallback(
        provider = provider,
        code = code,
        state = state,
    ))

# 인증 검사하기
@router.post('/checking/')
async def _checking(
    authorization: str = Header(None),
):
    from .post_checking import post_checking
    return await post_checking(types.PostChecking(
        authorization = authorization
    ))

# 패스워드 타입의 프로바이더 등록
@router.put('/')
async def _put_item(
    code: str = Form(..., pattern=Patterns.code),
    user_id: str = Form(..., alias='id', pattern=Patterns.code),
    user_name: str = Form(None, alias='name'),
    user_avatar: str = Form(None, alias='avatar', pattern=Patterns.url),
    user_email: str = Form(..., alias='email', pattern=Patterns.email),
    user_password: str = Form(..., alias='password'),
):
    from .put_item import put_item
    return await put_item(types.PutItem(
        code = code,
        user_id = user_id,
        user_name = user_name,
        user_avatar = user_avatar,
        user_email = user_email,
        user_password = user_password,
    ))

# 패스워드 타입의 프로바이더 인증
@router.post('/signin/')
async def _signin(
    user_id: str = Form(..., alias='id', pattern=Patterns.code),
    user_password: str = Form(..., alias='password'),
):
    from .post_signin import post_signin
    return await post_signin(types.PostSignin(
        user_id = user_id,
        user_password = user_password,
    ))

# 프로바이더 수정
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    user_id: str = Form(None, alias='id', pattern=Patterns.code),
    user_name: str = Form(None, alias='name'),
    user_avatar: str = Form(None, alias='avatar', pattern=Patterns.url),
    user_email: str = Form(None, alias='email', pattern=Patterns.email),
    user_password: str = Form(None, alias='password'),
):
    from .patch_item import patch_item
    return await patch_item(types.PatchItem(
        srl = srl,
        user_id = user_id,
        user_name = user_name,
        user_avatar = user_avatar,
        user_email = user_email,
        user_password = user_password,
    ))

# 프로바이더 삭제
@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    from .delete_item import delete_item
    return await delete_item(types.DeleteItem(srl = srl))

# 프로바이더 인증 웹소켓
@router.websocket('/ws/{socket_id:str}/')
async def _ws_index(ws: WebSocket, socket_id: str = Path(...)):
    from .ws_index import ws_index
    return await ws_index(ws, socket_id)

# for DEV
if __dev__:
    # 웹소켓 테스트
    @router.get('/test_websocket/')
    async def _test_auth():
        from fastapi.responses import HTMLResponse
        from pathlib import Path
        path = Path(__file__).parent / 'test_websocket.html'
        with open(path, 'r', encoding='utf-8') as file:
            content = file.read()
        return HTMLResponse(content=content, status_code=200)
