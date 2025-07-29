from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse, json_stringify
from src.modules.verify import checking_token

async def patch_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check item
        data = db.get_item(
            table_name=Table.NEST.value,
            where=[ f'srl = {params.srl}' ],
        )
        if not data: raise Exception('Item not found.', 204)

        # check app_srl
        if params.app_srl:
            count = db.get_count(
                table_name=Table.APP.value,
                where=[ f'srl = {params.app_srl}' ],
            )
            if count <= 0: raise Exception('Not found app.', 400)

        # check json_data
        json_data = json_parse(params.json_data) if params.json_data else None
        if params.json_data and json_data is None: raise Exception('Invalid JSON data.', 400)

        # set values
        values = {}
        if params.app_srl:
            values['app_srl'] = params.app_srl
        if params.code and data.get('code') != params.code:
            values['code'] = params.code
        if params.name:
            values['name'] = params.name
        if params.description:
            values['description'] = params.description
        if json_data:
            values['json'] = json_stringify(json_data)

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

        # set placeholder
        placeholders = []
        if 'app_srl' in values:
            placeholders.append('app_srl = :app_srl')
        if 'code' in values:
            placeholders.append('code = :code')
        if 'name' in values:
            placeholders.append('name = :name')
        if 'description' in values:
            placeholders.append('description = :description')
        if 'json' in values:
            placeholders.append('json = :json')

        # check exist code
        if 'code' in values and values['code']:
            count = db.get_count(
                table_name=Table.NEST.value,
                where=[ 'code LIKE :code' ],
                values={ 'code': values['code'] }
            )
            if count > 0: raise Exception('"code" already exists.', 400)

        # update item
        db.update_item(
            table_name=Table.NEST.value,
            where=[ f'srl = {params.srl}' ],
            placeholders=placeholders,
            values=values,
        )

        # set result
        result = output.success({
            'message': 'Complete update nest.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
