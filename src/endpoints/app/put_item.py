from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def put_item(params: types.AddItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # TODO: 인증 검사하기

        # check code already exists
        count = db.get_count(
            table_name = Table.APP.value,
            where = [ f' and code LIKE "{params.code}"' ],
        )
        if count > 0: raise Exception('code already exists')

        # set values
        values = {
            'code': params.code,
            'name': params.name,
            'description': params.description or '',
        }

        # set keys
        placeholders = [
            { 'key': 'code', 'value': ':code' },
            { 'key': 'name', 'value': ':name' },
            { 'key': 'description', 'value': ':description' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add item
        data = db.add_item(
            table_name = Table.APP.value,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success add item.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
