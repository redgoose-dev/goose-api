from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.check import parse_json, check_url
from src.libs.object import json_stringify

async def put_item(params: types.PutItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # check parse json
        json_data = parse_json(params.json_data)

        # check category_srl
        # TODO: check exist category data

        # check path
        if params.path: check_url(params.path)

        # set values
        values = {
            'category_srl': params.category_srl or None,
            'name': params.name or None,
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
            { 'key': 'created_at', 'value': 'CURRENT_TIMESTAMP' },
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
        db.disconnect()
        return result
