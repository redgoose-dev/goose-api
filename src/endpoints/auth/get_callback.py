import asyncio, json
from urllib.parse import urlencode
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.string import uri_decode, get_url
from .provider import get_token, get_user, check_user_id
from .ws_index import close_websocket_after_delay, ws_clients

async def get_callback(params: types.GetCallback, _db: DB = None):

    # set values
    result = None
    state = uri_decode(params.state)

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # get exist provider
        provider = db.get_item(
            table_name = Table.PROVIDER.value,
            where = [ f'code LIKE "{params.provider}"' ],
        )

        # get tokens
        tokens = await get_token(params.provider, params.code)

        # get user info
        user = await get_user(params.provider, tokens['access_token'])

        # check exist provider
        if provider:
            if not check_user_id(params.provider, provider['user_id'], user):
                raise Exception('Invalid user id.', 403)
            provider_srl = provider['srl']
        else:
            provider_srl = db.add_item(
                table_name = Table.PROVIDER.value,
                values = {
                    'code': params.provider,
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

        # add data from token
        db.add_item(
            table_name = Table.TOKEN.value,
            values = {
                'provider_srl': provider_srl,
                'access': tokens['access_token'],
                'expires': tokens['expires_in'],
                'refresh': tokens['refresh_token'] or None,
            },
            placeholders = [
                { 'key': 'provider_srl', 'value': ':provider_srl' },
                { 'key': 'access', 'value': ':access' },
                { 'key': 'expires', 'value': ':expires' },
                { 'key': 'refresh', 'value': ':refresh' },
                { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
            ],
        )

        # result
        data = {
            'access': tokens['access_token'],
            'refresh': tokens['refresh_token'],
            'expires': tokens['expires_in'],
        }
        if 'socket_id' in state:
            # send result to client from websocket
            ws = ws_clients[state['socket_id']]
            await ws.send_text(json.dumps({
                'mode': 'auth-complete',
                **data,
            }))
            # close websocket
            asyncio.create_task(close_websocket_after_delay(state['socket_id'], 5))
            result = 'Complete auth. Please close this window.'
        elif 'redirect_uri' in state:
            qs = urlencode(data)
            result = output.redirect(f'{state['redirect_uri']}?{qs}')
        else:
            result = output.success({
                'message': 'Complete auth.',
                **data,
            })
    except Exception as e:
        result = output.exc(e)
        if 'socket_id' in state:
            result = 'Failed auth. Please close this window.'
        elif 'redirect_uri' in state:
            qs = {}
            if 'error-code' in result.headers:
                qs['error'] = result.headers['error-code']
            result = output.redirect(f'{state['redirect_uri']}?{urlencode(qs)}')
    finally:
        if not _db: db.disconnect()
        return result
