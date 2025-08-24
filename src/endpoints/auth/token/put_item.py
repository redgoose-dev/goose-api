from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from ..provider.password import ProviderPassword

async def put_item(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PutItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # check provider_srl
        if not (token and token.get('provider_srl')):
            raise Exception('provider_srl not found', 401)

        # set token
        access = ProviderPassword.new_token('access', 'oo')
        refresh = ProviderPassword.new_token('refresh', 'oo')

        # add data from token
        srl = db.add_item(
            table_name=Table.TOKEN.value,
            values={
                'provider_srl': token.get('provider_srl'),
                'access': access,
                'expires': None,
                'refresh': refresh,
                'description': params.description,
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

        # get new token data
        new_token = db.get_item(
            table_name=Table.TOKEN.value,
            where=[ f'srl = {srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete create public token.',
            'data': {
                'srl': new_token['srl'],
                'provider_srl': new_token['provider_srl'],
                'access': new_token['access'],
                'description': new_token['description'],
            },
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
