import json, asyncio
from urllib.parse import urlencode
from fastapi import WebSocket
from src.libs.object import json_parse
from src.libs.string import get_url, uri_encode
from .provider import Provider

ws_clients: dict = {}
ws_timeout: int = 30 # seconds

def make_auth_link(provider: str, socket_id: str):
    # set provider instance
    _provider_ = Provider(provider)
    # check values
    if not _provider_.client_id:
        raise Exception('Can\'t get client_id.', 400)
    if not _provider_.client_secret:
        raise Exception('Can\'t get client_secret.', 400)
    if not socket_id:
        raise Exception('Can\'t get socket_id.', 400)
    # set query string
    url = _provider_.create_authorize_url(uri_encode({'socket_id': socket_id}))
    return url

async def ws_index(ws: WebSocket, socket_id: str):
    await ws.accept()
    ws_clients[socket_id] = ws
    if not ws: return
    try:
        while True:
            data = await ws.receive_text()
            data = json_parse(data)
            if not ('mode' in data): continue
            match data['mode']:
                case 'start-auth':
                    path = make_auth_link(data['provider'], socket_id)
                    await ws.send_text(json.dumps({
                        'mode': 'auth-link',
                        'url': path,
                        'timeout': ws_timeout,
                    }))
            await asyncio.sleep(ws_timeout)
            raise Exception('timeout')
    except Exception as _:
        if socket_id in ws_clients: del ws_clients[socket_id]
        try: await ws.close()
        except Exception: pass

async def close_websocket_after_delay(socket_id: str, seconds: int = 5):
    await asyncio.sleep(seconds)  # 5초 대기
    if socket_id in ws_clients:
        ws = ws_clients[socket_id]
        await ws.send_text(json.dumps({
            'mode': 'closing',
            'message': f'Connection will close after {seconds} seconds.',
        }))
        try: await ws.close()
        except Exception: pass
        del ws_clients[socket_id]
