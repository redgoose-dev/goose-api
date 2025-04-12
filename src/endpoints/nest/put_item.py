from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse, json_stringify
from src.modules.verify import checking_token

async def put_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PutItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check parse json
        json_data = json_parse(params.json_data) if params.json_data else None
        if params.json_data and not json_data: raise Exception('Invalid JSON data.', 400)

        # check app_srl
        count = db.get_count(
            table_name=Table.APP.value,
            where=[ f'srl = {params.app_srl}' ],
        )
        if count <= 0: raise Exception('Not found app.', 400)

        # check code
        count = db.get_count(
            table_name=Table.NEST.value,
            where=[ f'code LIKE "{params.code}"' ],
        )
        if count > 0: raise Exception('Exist code in nest.', 400)

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
            table_name=Table.NEST.value,
            placeholders=placeholders,
            values=values,
        )

        # set result
        result = output.success({
            'message': 'Complete add nest.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
