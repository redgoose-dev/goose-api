from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from ..file import __libs__ as file_libs
from ..tag import __libs__ as tag_libs

async def delete_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check data
        count = db.get_count(
            table_name = Table.CHECKLIST.value,
            where = [ f'srl = {params.srl}' ],
        )
        if count == 0: raise Exception('Item not found.', 204)

        # delete data
        db.delete_item(
            table_name = Table.CHECKLIST.value,
            where = [ f'srl = {params.srl}' ],
        )

        # delete files
        file_libs.delete(db, file_libs.Module.CHECKLIST, params.srl)

        # delete tag
        tag_libs.delete_all(db, tag_libs.Module.CHECKLIST, params.srl)

        # set result
        result = output.success({
            'message': 'Complete delete checklist item.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
