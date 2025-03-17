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

        # set data
        data = db.get_item(
            table_name = Table.CATEGORY.value,
            fields = fields,
            where = [ f'and srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete get Category item.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
