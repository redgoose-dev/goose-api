from fastapi import Request
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from src.modules.preference import Preference
from .provider import Provider

async def post_checking(req: Request, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # checking token
        token = checking_token(req, db)

        # set preference
        pref = Preference()

        # get provider
        provider = db.get_item(
            table_name = Table.PROVIDER.value,
            where = [ f'srl = {token['provider_srl']}' ],
        )
        if not provider: raise Exception('No provider', 401)

        # set provider instance
        _provider_ = Provider(provider['code'])

        # set result
        result = output.success({
            'message': 'Complete checking auth.',
            'data': {
                'provider': {
                    'srl': provider['srl'],
                    'name': _provider_.name,
                    'user_id': provider['user_id'],
                    'user_name': provider['user_name'],
                    'user_avatar': provider['user_avatar'],
                    'user_email': provider['user_email'],
                },
                'preference': pref.get_all(),
            },
        })
    except Exception as e:
        result = output.exc(e)
        if result.status_code == 401:
            result = output.empty({ 'code': 202 })
    finally:
        if not _db and db: db.disconnect()
        return result
