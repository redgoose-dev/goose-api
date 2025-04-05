from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .provider import Provider

async def post_ready_login(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PostReadyLogin(**params)

        # get providers
        providers = Provider.get_providers()

        # set providers data
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
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
