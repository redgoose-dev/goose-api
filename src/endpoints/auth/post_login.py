from src import output
from src.libs.db import DB, Table
from . import __types__ as types
from .provider import Provider
from .provider.password import ProviderPassword

async def post_login(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PostLogin(**params)

        # set provider instance
        _provider_ = Provider(Provider.code_password)

        # get provider
        provider = db.get_item(
            table_name=Table.PROVIDER.value,
            where=[
                f'AND user_id LIKE \'{params.user_id}\'',
                f'AND code LIKE \'{_provider_.name}\'',
            ],
        )
        if not provider: raise Exception('Provider not found.', 401)

        # verify password
        verifyed = ProviderPassword.verify_password(provider.get('user_password'), params.user_password)
        if not verifyed: raise Exception('Failed to verify password.', 401)

        # create new tokens
        new_token = ProviderPassword.create_token()

        # add token data
        db.add_item(
            table_name = Table.TOKEN.value,
            values={
                'provider_srl': provider.get('srl'),
                'access': new_token.get('access'),
                'expires': new_token.get('expires'),
                'refresh': new_token.get('refresh'),
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
            'message': 'Complete login.',
            'data': {
                'access': new_token.get('access'),
                'expires': new_token.get('expires'),
                'refresh': new_token.get('refresh'),
            },
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
