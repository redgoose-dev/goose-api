from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def delete_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # get item
        item = db.get_item(
            table_name=Table.TOKEN.value,
            where=[
                f'srl = {params.srl}',
                f'AND expires IS NULL'
            ],
        )
        if not item: raise Exception('Item not found', 400)

        # delete data
        db.update_item(
            table_name=Table.TOKEN.value,
            where=[ f'srl = {params.srl}' ],
            placeholders=[ 'expires = :expires' ],
            values={ 'expires': 0 },
        )

        # set result
        result = output.success({
            'message': 'The token has expired.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
