from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __types__ as types, __libs__ as json_libs

async def delete_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # set where
        where = [ f'srl = {params.srl}' ]

        # check item
        count = db.get_count(
            table_name=Table.JSON.value,
            where=where,
        )
        if count == 0: raise Exception('No data', 204)

        # delete data
        json_libs.delete(db, params.srl)

        # set result
        result = output.success({
            'message': 'Complete delete JSON.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
