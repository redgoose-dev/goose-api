from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def get_item(params: types.GetItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # set fields
        fields = params.fields.split(',') if params.fields else None

        # get data
        data = db.get_item(
            table_name = Table.COMMENT.value,
            fields = fields,
            where = [ f'srl = {params.srl}' ],
        )
        if not data: raise Exception('Item not found', 204)

        # set result
        result = output.success({
            'message': 'Complete get comment item.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
