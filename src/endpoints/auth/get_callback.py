import asyncio, json
from urllib.parse import urlencode
from src import output
from src.libs.db import DB, Table
from src.libs.string import uri_decode
from src.modules.verify import checking_token
from . import __types__ as types
from .provider import Provider
from .ws_index import close_websocket_after_delay, ws_clients

async def get_callback(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    state = {}
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetCallback(**params)
        state = uri_decode(params.state)

        # set provider instance
        _provider_ = Provider(params.provider)

        # get tokens
        token = await _provider_.get_token(params.code)

        # get user info
        user = await _provider_.get_user(token['access'])

        # check provider count
        count = db.get_count(table_name=Table.PROVIDER.value)
        if count > 0:
            # 프로바이더가 하나 이상일때
            # checking access token
            account = None
            if state.get('access_token'):
                account = checking_token(
                    req=req,
                    db=db,
                    access_token=state.get('access_token', ''),
                )
            # get provider
            provider = db.get_item(
                table_name=Table.PROVIDER.value,
                where=[
                    f'and code LIKE \'{params.provider}\'',
                    f'and user_id LIKE \'{user.get('id')}\'',
                ],
            )
            if provider:
                provider_srl = provider.get('srl')
            elif account:
                # 만들어진 프로바이더가 없으니 새로운 프로바이더를 만든다.
                provider_srl = db.add_item(
                    table_name=Table.PROVIDER.value,
                    values={
                        'code': _provider_.name,
                        'user_id': user['id'],
                        'user_name': user['name'],
                        'user_avatar': user['avatar'],
                        'user_email': user['email'],
                        'user_password': None,
                    },
                    placeholders=[
                        {'key': 'code', 'value': ':code'},
                        {'key': 'user_id', 'value': ':user_id'},
                        {'key': 'user_name', 'value': ':user_name'},
                        {'key': 'user_avatar', 'value': ':user_avatar'},
                        {'key': 'user_email', 'value': ':user_email'},
                        {'key': 'user_password', 'value': ':user_password'},
                        {'key': 'created_at', 'value': 'DATETIME("now", "localtime")'},
                    ],
                )
            else:
                raise Exception('Invalid user id.', 401)

        else:
            # 프로바이더가 하나도 없을때
            provider_srl = db.add_item(
                table_name=Table.PROVIDER.value,
                values={
                    'code': _provider_.name,
                    'user_id': user['id'],
                    'user_name': user['name'],
                    'user_avatar': user['avatar'],
                    'user_email': user['email'],
                    'user_password': None,
                },
                placeholders = [
                    { 'key': 'code', 'value': ':code' },
                    { 'key': 'user_id', 'value': ':user_id' },
                    { 'key': 'user_name', 'value': ':user_name' },
                    { 'key': 'user_avatar', 'value': ':user_avatar' },
                    { 'key': 'user_email', 'value': ':user_email' },
                    { 'key': 'user_password', 'value': ':user_password' },
                    { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
                ],
            )

        # check provider_srl
        if not provider_srl:
            raise Exception('Not found provider_srl.', 401)

        # add data from token
        db.add_item(
            table_name=Table.TOKEN.value,
            values={
                'provider_srl': provider_srl,
                'access': token['access'],
                'expires': token['expires'],
                'refresh': token['refresh'] or None,
                'description': f'OAuth by {_provider_.name}',
            },
            placeholders=[
                { 'key': 'provider_srl', 'value': ':provider_srl' },
                { 'key': 'access', 'value': ':access' },
                { 'key': 'expires', 'value': ':expires' },
                { 'key': 'refresh', 'value': ':refresh' },
                { 'key': 'description', 'value': ':description' },
                { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
            ],
        )

        # result
        data = {
            'provider_srl': provider_srl,
            'access': token['access'],
            'expires': token['expires'],
            'refresh': token['refresh'],
        }
        if 'socket_id' in state:
            # send result to client from websocket
            ws = ws_clients[state['socket_id']]
            await ws.send_text(json.dumps({
                'mode': 'auth-complete',
                **data,
            }))
            # close websocket
            asyncio.create_task(close_websocket_after_delay(state['socket_id'], 2))
            result = output.text('Complete auth. Please close this window.', _req=req)
        elif 'redirect_uri' in state:
            qs = urlencode(data)
            result = output.redirect(f'{state['redirect_uri']}?{qs}', _req=req)
        else:
            result = output.success({
                'message': 'Complete auth.',
                **data,
            }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
        if 'socket_id' in state:
            ws = ws_clients[state['socket_id']]
            data = {
                'status_code': result.status_code,
                'message': 'Failed auth.',
            }
            if 'error-code' in result.headers:
                data['error_code'] = result.headers['error-code']
            await ws.send_text(json.dumps({
                'mode': 'auth-error',
                **data,
            }))
            asyncio.create_task(close_websocket_after_delay(state['socket_id'], 1))
            result = 'Failed auth. Please close this window.'
        elif 'redirect_uri' in state:
            qs = {}
            if 'error-code' in result.headers:
                qs['error_code'] = result.headers['error-code']
            result = output.redirect(f'{state['redirect_uri']}?{urlencode(qs)}', _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
