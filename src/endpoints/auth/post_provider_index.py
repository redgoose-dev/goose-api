from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import list_to_dict
from src.libs.util import jprint
from src.modules.verify import checking_token
from .provider import Provider

async def post_provider_index(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PostProviderIndex(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # set providers
        providers = Provider.get_providers()

        # get index
        data = db.get_items(
            table_name=Table.PROVIDER.value,
            unlimited=True,
        )
        if not (data and len(data) > 0): raise Exception('No data', 204)
        data = list_to_dict(data, 'code')

        # transform items
        def transform_item(provider: str) -> dict:
            _item = { 'code': provider }
            account = data.get(provider)
            if account:
                if 'user_password' in account: del account['user_password']
                _item['account'] = account
            else:
                _item['auth_url'] = Provider.get_authorize_url(provider, params.redirect_uri)
            return _item
        index = [ transform_item(provider) for provider in providers ]

        # set result
        result = output.success({
            'message': 'Complete get provider index.',
            'data': {
                'index': index,
            },
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
