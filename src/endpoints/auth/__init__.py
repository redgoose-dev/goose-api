from fastapi import APIRouter, Query, Path, WebSocket
from src import __dev__
from src.libs.resource import Patterns
from . import __types__ as types

# setters
router = APIRouter()

@router.get('/')
async def _get_index(
    fields: str = Query(None, pattern=Patterns.fields),
):
    from .get_index import get_index
    return await get_index(types.GetIndex(
        fields = fields,
    ))

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

# checking auth
@router.post('/checking/')
async def _checking():
    from .post_checking import post_checking
    # TODO: 인증과 엑세스 토큰 검사하고 사용자 정보 가져오기
    # TODO: result - 계정정보
    # TODO: result - 상태 (로그인,로그아웃)
    return await post_checking(types.PostChecking(
        #
    ))

@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    from .delete_item import delete_item
    # TODO: 테스트 해야함
    return await delete_item(types.DeleteItem(
        srl = srl,
    ))

@router.websocket('/ws/{socket_id:str}/')
async def _ws_index(ws: WebSocket, socket_id: str = Path(...)):
    from .ws_index import ws_index
    return await ws_index(ws, socket_id)

# for DEV
if __dev__:
    from fastapi.responses import HTMLResponse
    @router.get('/test_websocket/')
    async def _test_auth():
        from pathlib import Path
        path = Path(__file__).parent / 'test_websocket.html'
        with open(path, 'r', encoding='utf-8') as file:
            content = file.read()
        return HTMLResponse(content=content, status_code=200)
