from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __types__ as types, __libs__ as checklist_libs

async def delete_item(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # check data
        count = db.get_count(
            table_name=Table.CHECKLIST.value,
            where=[ f'srl = {params.srl}' ],
        )
        if count == 0: raise Exception('Item not found.', 204)

        # delete data
        checklist_libs.delete(db, params.srl)

        # set result
        result = output.success({
            'message': 'Complete delete checklist.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
