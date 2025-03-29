from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .provider import Provider

async def post_renew(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PostRenew(**params)

        # get token
        token = db.get_item(
            table_name = Table.TOKEN.value,
            where = [ f'access LIKE "{params.access_token}"' ],
        )

        # get provider
        provider = db.get_item(
            table_name = Table.PROVIDER.value,
            where = [ f'srl = {token['provider_srl']}' ],
        )

        # check provider
        if params.provider != provider['code']:
            raise Exception('Provider does not match.', 400)

        # set provider instance
        _provider_ = Provider(provider['code'])

        # create new access token
        new_token = await _provider_.renew_access_token(
            refresh_token = params.refresh_token,
        )
        if not new_token: raise Exception('Failed to renew access token.', 400)

        # expires to zero from old token
        db.update_item(
            table_name = Table.TOKEN.value,
            where = [ f'srl = {token['srl']}' ],
            placeholders = [ 'expires = :expires' ],
            values = { 'expires': 0 },
        )

        # add token
        db.add_item(
            table_name = Table.TOKEN.value,
            values = {
                'provider_srl': token['provider_srl'],
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
            'message': 'Complete token renewed.',
            'data': {
                'access': new_token['access'],
                'refresh': new_token['refresh'],
                'expires': new_token['expires'],
            }
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
