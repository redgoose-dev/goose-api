from . import __types__ as types
from src import output
from src.libs.db import DB, Table
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
            table_name = Table.PROVIDER.value,
            where = [
                f'and user_id LIKE "{params.user_id}"',
                f'and code LIKE "{_provider_.name}"'
            ],
        )
        if not provider: raise Exception('Provider not found.', 204)

        # verify password
        verifyed = ProviderPassword.verify_password(provider['user_password'], params.user_password)
        if not verifyed: raise Exception('Failed to verify password.', 401)

        # create new tokens
        new_token = ProviderPassword.create_token()

        # add token data
        db.add_item(
            table_name = Table.TOKEN.value,
            values = {
                'provider_srl': provider['srl'],
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
            'message': 'Complete login.',
            'data': {
                'access': new_token['access'],
                'expires': new_token['expires'],
                'refresh': new_token['refresh'],
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
