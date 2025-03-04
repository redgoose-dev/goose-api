from . import __types__ as types
from src import output
from src.libs.db import DB

async def add_item(params: types.AddItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # check id already exists
        count = db.get_count(
            table_name = 'app',
            where = [ f' and id="{params.id}"' ],
        )
        if count > 0: raise Exception('id already exists')

        # set values
        values = {
            'id': params.id,
            'name': params.name or '',
            'description': params.description or '',
        }

        # set keys
        placeholders = [
            { 'key': 'id', 'value': ':id' },
            { 'key': 'name', 'value': ':name' },
            { 'key': 'description', 'value': ':description' },
            { 'key': 'created_at', 'value': 'CURRENT_TIMESTAMP' },
        ]

        # add item
        data = db.add_item(
            table_name = 'app',
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success add item.',
            'data': data,
        })
    except Exception as e:
        result = output.error(None, {
            'error': e,
        })
    finally:
        db.disconnect()
        return result
