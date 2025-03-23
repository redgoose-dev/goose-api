from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__libs__ import hash_password, create_tokens
from .provider import ProviderCode

async def put_item(params: types.PutItem, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # check exist provider
        count = db.get_count(
            table_name = Table.PROVIDER.value,
            where = [ f'code like "{ProviderCode.password}"' ],
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

        # new tokens
        tokens = create_tokens(db, provider_srl)

        # set result
        result = output.success({
            'message': 'Complete add provider.',
            'data': {
                'access': tokens['access'],
                'refresh': tokens['refresh'],
                'expires': tokens['expires'],
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
