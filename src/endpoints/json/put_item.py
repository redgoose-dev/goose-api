from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.check import check_url
from src.libs.object import json_parse, json_stringify
from src.modules.verify import checking_token

async def put_item(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PutItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # check parse json
        json_data = json_parse(params.json_data)

        # check category_srl
        if params.category_srl:
            from ..category import __libs__ as category_libs
            count = db.get_count(
                table_name = Table.CATEGORY.value,
                where = [
                    f'AND module LIKE \'{category_libs.Module.JSON}\'',
                    f'AND srl = {params.category_srl}',
                ],
            )
            if not (count > 0): raise Exception('Invalid category_srl', 400)

        # set values
        values = {
            'category_srl': params.category_srl or None,
            'name': params.name,
            'description': params.description or None,
            'json': json_stringify(json_data, None) or '{}',
        }

        # set placeholders
        placeholders = [
            { 'key': 'category_srl', 'value': ':category_srl' },
            { 'key': 'name', 'value': ':name' },
            { 'key': 'description', 'value': ':description' },
            { 'key': 'json', 'value': ':json' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
            { 'key': 'updated_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add item
        data = db.add_item(
            table_name = Table.JSON.value,
            placeholders = placeholders,
            values = values,
        )

        # add tag
        if params.tag:
            from ..tag import __libs__ as tag_libs
            tag_libs.add(
                _db = db,
                tags = params.tag,
                module = tag_libs.Module.JSON,
                module_srl = data,
            )

        # set result
        result = output.success({
            'message': 'Complete add JSON.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
