from src import output
from src.libs.db import DB, Table
from src.libs.object import list_to_dict
from src.libs.util import jprint, get_authorization
from src.modules.verify import checking_token
from . import __types__ as types
from .provider import Provider

async def get_provider(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetProviderItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # set where
        srl = token.get('provider_srl') if token else None
        if params.srl:
            srl = params.srl
        elif not token:
            authorization = get_authorization(req)
            token = db.get_item(
                table_name=Table.TOKEN.value,
                where=[ f'access LIKE \'{authorization}\'' ],
            )
            srl = token.get('provider_srl', None)

        # check token data
        if not srl:
            raise Exception('No token data.', 400)

        # set where
        where = [ f'srl = {srl}' ]

        # get provider
        data = db.get_item(
            table_name=Table.PROVIDER.value,
            where=where,
        )
        if not data: raise Exception('No data', 204)
        del data['user_password']

        # set result
        result = output.success({
            'message': 'Complete get provider.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
