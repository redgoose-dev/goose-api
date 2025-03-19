from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def delete_item(params: types.DeleteItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # get item
        item = db.get_item(
            table_name = Table.COMMENT.value,
            where = [ f'srl = {params.srl}' ],
        )
        if not item: raise Exception('Item not found', 204)

        # delete item
        db.delete_item(
            table_name = Table.COMMENT.value,
            where = [ f'srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Success delete Comment.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
