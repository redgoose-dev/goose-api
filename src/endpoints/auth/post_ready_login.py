from src import output
from src.libs.db import DB, Table
from . import __types__ as types
from .provider import Provider

async def post_ready_login(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PostReadyLogin(**params)

        # get index
        data = db.get_items(
            table_name=Table.PROVIDER.value,
            unlimited=True,
        )

        # set providers data
        if len(data) > 0:
            def transform_item(item: str) -> dict:
                _result = { 'name': item.get('code') }
                if item.get('code') != 'password':
                    _result['auth_url'] = Provider.get_authorize_url(item.get('code'), params.redirect_uri)
                return _result
            providers = [ transform_item(item) for item in data ]
        else:
            providers = Provider.get_providers()
            def transform_item(provider: str) -> dict:
                _result = { 'name': provider }
                if provider != 'password':
                    _result['auth_url'] = Provider.get_authorize_url(provider, params.redirect_uri)
                return _result
            providers = [ transform_item(provider) for provider in providers ]

        # set result
        result = output.success({
            'message': 'Complete get ready login data.',
            'data': {
                'providers': providers,
            },
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
