from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def put_item(params: types.PutItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # TODO: 인증 검사하기

        # get max turn
        where = [
            f'and module LIKE "{params.module}"',
            f'and target_srl={params.target_srl or 0}'
        ]
        count = db.get_count(
            table_name = Table.CATEGORY.value,
            where = where,
        )

        # set values
        values = {
            'target_srl': params.target_srl or 0,
            'name': params.name,
            'module': params.module,
            'turn': count + 1,
        }

        # set placeholders
        placeholders = [
            { 'key': 'target_srl', 'value': ':target_srl' },
            { 'key': 'turn', 'value': ':turn' },
            { 'key': 'name', 'value': ':name' },
            { 'key': 'module', 'value': ':module' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add item
        data = db.add_item(
            table_name = Table.CATEGORY.value,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success add Category.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
