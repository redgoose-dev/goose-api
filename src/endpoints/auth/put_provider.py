from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from .provider.password import ProviderPassword
from .provider import Provider

async def put_provider(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PutProvider(**params)

        # set provider instance
        _provider_ = Provider(Provider.code_password)

        # check exist provider
        count = db.get_count(
            table_name=Table.PROVIDER.value,
            where=[ f'code LIKE \'{_provider_.name}\'' ],
        )
        if count > 0: raise Exception('Exist provider data', 400)

        # check provider
        count = db.get_count(
            table_name=Table.PROVIDER.value,
        )
        if count > 0 and _check_token: checking_token(req, db)

        # set password
        password = ProviderPassword.hash_password(params.user_password)

        # set values
        values = {
            'code': _provider_.name,
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

        # create new tokens
        new_token = ProviderPassword.create_token()

        # add token data
        db.add_item(
            table_name = Table.TOKEN.value,
            values = {
                'provider_srl': provider_srl,
                'access': new_token['access'],
                'expires': new_token['expires'],
                'refresh': new_token['refresh'],
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
            'message': 'Complete add provider.',
            'data': {
                'access': new_token['access'],
                'expires': new_token['expires'],
                'refresh': new_token['refresh'],
            },
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
