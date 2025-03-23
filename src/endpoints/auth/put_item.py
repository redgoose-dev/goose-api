from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__libs__ import hash_password, create_token
from src.libs.number import time_to_seconds
from .provider import ProviderCode

async def put_item(params: types.PutItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # check provider code
        if not ProviderCode.check_exist(params.code):
            raise Exception('Invalid provider code', 400)

        # check exist provider
        count = db.get_count(
            table_name = Table.PROVIDER.value,
            where = [
                f'and code like "{ProviderCode.password}"',
                f'and user_id like "{params.user_id}"',
            ],
        )
        if count > 0: raise Exception('Exist provider data', 400)

        # set password
        password = hash_password(params.user_password)

        # set values
        values = {
            'code': ProviderCode.password,
            'user_id': params.user_id,
            'user_name': params.user_name,
            'user_avatar': params.user_avatar,
            'user_email': params.user_email,
            'user_password': password,
        }

        # set placeholders
        placeholders = [
            { 'key': 'code', 'value': ':code' },
            { 'key': 'user_id', 'value': ':user_id' },
            { 'key': 'user_name', 'value': ':user_name' },
            { 'key': 'user_avatar', 'value': ':user_avatar' },
            { 'key': 'user_email', 'value': ':user_email' },
            { 'key': 'user_password', 'value': ':user_password' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add provider data
        provider_srl = db.add_item(
            table_name = Table.PROVIDER.value,
            placeholders = placeholders,
            values = values,
        )

        # set tokens
        access_token = create_token('access')
        refresh_token = create_token('refresh')
        expires = time_to_seconds('day', 7)

        # add token
        db.add_item(
            table_name = Table.TOKEN.value,
            values = {
                'provider_srl': provider_srl,
                'access': access_token,
                'expires': expires,
                'refresh': refresh_token,
            },
            placeholders = [
                { 'key': 'provider_srl', 'value': ':provider_srl' },
                { 'key': 'access', 'value': ':access' },
                { 'key': 'expires', 'value': ':expires' },
                { 'key': 'refresh', 'value': ':refresh' },
                { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
            ],
        )

        # set result
        result = output.success({
            'message': 'Complete add Provider.',
            'data': {
                'access_token': access_token,
                'refresh_token': refresh_token,
                'expires': expires,
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
