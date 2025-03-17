from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.check import parse_json, check_url
from src.libs.object import json_stringify

async def put_item(params: types.PutItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    print('PARAMS: ', params)

    try:
        # check parse json
        json_data = parse_json(params.json_data)

        # check category_srl
        if params.category_srl:
            count = db.get_count(
                table_name = Table.CATEGORY.value,
                where = [
                    'and module LIKE "json"',
                    f'and srl = {params.category_srl}',
                ],
            )
            if not (count > 0): raise Exception('Invalid category_srl', 400)

        # check path
        if params.path: check_url(params.path)

        # set values
        values = {
            'category_srl': params.category_srl or None,
            'name': params.name,
            'description': params.description or None,
            'json': json_stringify(json_data, None) or '{}',
            'path': params.path or None,
        }

        # set placeholders
        placeholders = [
            { 'key': 'category_srl', 'value': ':category_srl' },
            { 'key': 'name', 'value': ':name' },
            { 'key': 'description', 'value': ':description' },
            { 'key': 'json', 'value': ':json' },
            { 'key': 'path', 'value': ':path' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add item
        data = db.add_item(
            table_name = Table.JSON.value,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success add JSON.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
