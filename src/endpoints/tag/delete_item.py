from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __libs__ as tag_libs

async def delete_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check module data
        count = db.get_count(
            table_name=tag_libs.Module.get_table_name(params.module),
            where=[ f'srl = {params.module_srl}' ],
        )
        if not (count > 0): raise Exception('Module data not found.', 204)

        # delete data
        tag_libs.delete(db, params.module, params.module_srl)

        # set result
        result = output.success({
            'message': 'Complete delete tag.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
