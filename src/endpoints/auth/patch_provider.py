from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __types__ as types
from .provider.password import ProviderPassword

async def patch_provider(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchProviderItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check data
        count = db.get_count(
            table_name=Table.PROVIDER.value,
            where=[ f'srl = {params.srl}' ],
        )
        if not (count > 0): raise Exception('Data not found.', 204)

        # check exist id
        count = db.get_count(
            table_name=Table.PROVIDER.value,
            where=[ f'user_id LIKE \'{params.user_id}\'' ],
        )
        if count > 0: raise Exception('Exist provider data', 400)

        # set values
        values = {}
        if params.user_id:
            values['user_id'] = params.user_id
        if params.user_name:
            values['user_name'] = params.user_name
        if params.user_avatar:
            values['user_avatar'] = params.user_avatar
        if params.user_email:
            values['user_email'] = params.user_email
        if params.user_password:
            values['user_password'] = ProviderPassword.hash_password(params.user_password)

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

        # set placeholder
        placeholders = []
        if 'user_id' in values and values['user_id']:
            placeholders.append('user_id = :user_id')
        if 'user_name' in values and values['user_name']:
            placeholders.append('user_name = :user_name')
        if 'user_avatar' in values and values['user_avatar']:
            placeholders.append('user_avatar = :user_avatar')
        if 'user_email' in values and values['user_email']:
            placeholders.append('user_email = :user_email')
        if 'user_password' in values and values['user_password']:
            placeholders.append('user_password = :user_password')

        # update data
        db.update_item(
            table_name=Table.PROVIDER.value,
            placeholders=placeholders,
            values=values,
            where=[ f'srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete update provider.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
