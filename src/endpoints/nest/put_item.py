from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.check import parse_json
from src.libs.object import json_stringify

async def put_item(params: types.PutItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # TODO: 인증 검사하기

        # check parse json
        json_data = parse_json(params.json_data) if params.json_data else {}

        # check app_srl
        count = db.get_count(
            table_name = Table.APP.value,
            where = [ f'srl={params.app_srl}' ],
        )
        if count <= 0: raise Exception('Not found App', 409)

        # check code
        count = db.get_count(
            table_name = Table.NEST.value,
            where = [ f'code LIKE "{params.code}"' ],
        )
        if count > 0: raise Exception('Exist code in Nest.', 409)

        # set values
        values = {
            'app_srl': params.app_srl,
            'code': params.code,
            'name': params.name,
            'description': params.description or None,
            'json': json_stringify(json_data, None) or '{}',
        }

        # set placeholders
        placeholders = [
            { 'key': 'app_srl', 'value': ':app_srl' },
            { 'key': 'code', 'value': ':code' },
            { 'key': 'name', 'value': ':name' },
            { 'key': 'description', 'value': ':description' },
            { 'key': 'json', 'value': ':json' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add item
        data = db.add_item(
            table_name = Table.NEST.value,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success add Nest.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
