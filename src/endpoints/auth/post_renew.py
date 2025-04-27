from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __types__ as types
from .provider import Provider

async def post_renew(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PostRenew(**params)

        # checking token
        token = checking_token(req, db, access_token=params.authorization, check_expires=False)

        # check refresh token
        if token.get('refresh') != params.refresh_token:
            raise Exception('Invalid refresh token.', 400)

        # get provider
        provider = db.get_item(
            table_name=Table.PROVIDER.value,
            where=[ f'srl = {token['provider_srl']}' ],
        )
        if not provider: raise Exception('Provider not found.', 400)

        # set provider instance
        _provider_ = Provider(provider.get('code'))

        # create new access token
        new_token = await _provider_.renew_access_token(
            refresh_token=params.refresh_token,
        )
        if not new_token: raise Exception('Failed to renew access token.', 400)

        # 이전 토큰의 만료시간을 0로 변경한다.
        db.update_item(
            table_name=Table.TOKEN.value,
            where=[ f'srl = {token['srl']}' ],
            placeholders=[ 'expires = :expires' ],
            values={ 'expires': 0 },
        )

        # add token
        db.add_item(
            table_name=Table.TOKEN.value,
            values={
                'provider_srl': token['provider_srl'],
                'access': new_token['access'],
                'expires': new_token['expires'],
                'refresh': new_token['refresh'],
            },
            placeholders=[
                { 'key': 'provider_srl', 'value': ':provider_srl' },
                { 'key': 'access', 'value': ':access' },
                { 'key': 'expires', 'value': ':expires' },
                { 'key': 'refresh', 'value': ':refresh' },
                { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
            ],
        )

        # set result
        result = output.success({
            'message': 'Complete renew token.',
            'data': {
                'access': new_token['access'],
                'refresh': new_token['refresh'],
                'expires': new_token['expires'],
            }
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
