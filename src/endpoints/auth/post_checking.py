from fastapi import Request
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from .provider import Provider

async def post_checking(req: Request, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # checking token
        token = checking_token(req, db)

        # get provider
        provider = db.get_item(
            table_name=Table.PROVIDER.value,
            where=[ f'srl = {token.get('provider_srl')}' ],
        )
        if not provider: raise Exception('No provider', 204)

        # set provider instance
        _provider_ = Provider(provider.get('code'))

        # set result
        result = output.success({
            'message': 'Complete checking auth.',
            'data': {
                'provider': {
                    'srl': provider.get('srl'),
                    'name': _provider_.name,
                    'user_id': provider.get('user_id'),
                    'user_name': provider.get('user_name'),
                    'user_avatar': provider.get('user_avatar'),
                    'user_email': provider.get('user_email'),
                },
            },
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
        if result.status_code == 401:
            result = output.empty({ 'code': 401 }, _req=req, _log=True)
    finally:
        if not _db and db: db.disconnect()
        return result
