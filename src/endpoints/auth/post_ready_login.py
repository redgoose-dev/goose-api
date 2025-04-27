from src import output
from src.libs.db import DB, Table
from . import __types__ as types
from .provider import Provider

async def post_ready_login(params: dict = {}, req = None):

    # set values
    result = None

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
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        return result
