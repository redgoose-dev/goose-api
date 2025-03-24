from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def delete_item(params: types.DeleteItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        checking_token(req, db)

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
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
