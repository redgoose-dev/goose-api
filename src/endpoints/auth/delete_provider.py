from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def delete_provider(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteProviderItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check data
        count = db.get_count(
            table_name = Table.PROVIDER.value,
            where = [ f'srl = {params.srl}' ],
        )
        if not (count > 0): raise Exception('Item not found.', 204)

        # delete data
        db.delete_item(
            table_name = Table.PROVIDER.value,
            where = [ f'srl = {params.srl}' ],
        )

        # delete tokens
        db.delete_item(
            table_name = Table.TOKEN.value,
            where = [ f'provider_srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete delete provider.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
