from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__libs__ import verify_password, create_tokens
from .provider import ProviderCode

async def post_login(params: types.PostLogin, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # get provider
        provider = db.get_item(
            table_name = Table.PROVIDER.value,
            where = [
                f'and user_id LIKE "{params.user_id}"',
                f'and code LIKE "{ProviderCode.password}"'
            ],
        )
        if not provider: raise Exception('Provider not found.', 204)

        # verify password
        verifyed = verify_password(provider['user_password'], params.user_password)
        if not verifyed: raise Exception('Failed to verify password.', 401)

        # create new tokens
        tokens = create_tokens(db, provider['srl'])

        # set result
        result = output.success({
            'message': 'Complete login.',
            'data': {
                'access': tokens['access'],
                'refresh': tokens['refresh'],
                'expires': tokens['expires'],
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
