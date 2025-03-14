from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse, json_stringify

async def patch_item(params: types.PatchItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # check item
        count = db.get_count(
            table_name = Table.NEST.value,
            where = [ f'srl={params.srl}' ],
        )
        if count <= 0: raise Exception('Item not found.', 204)

        # check app_srl
        if params.app_srl:
            count = db.get_count(
                table_name = Table.APP.value,
                where = [ f'srl={params.app_srl}' ],
            )
            if count <= 0: raise Exception('Not found App', 400)

        # check code
        if params.code:
            count = db.get_count(
                table_name = Table.NEST.value,
                where = [ f'code LIKE "{params.code}"' ],
            )
            if count > 0: raise Exception('Exist code in Nest.', 400)

        # check json_data
        json_data = json_parse(params.json_data) if params.json_data else None

        # set values
        values = {}
        if params.app_srl:
            values['app_srl'] = params.app_srl
        if params.code:
            values['code'] = params.code
        if params.name:
            values['name'] = params.name
        if params.description:
            values['description'] = params.description
        if json_data:
            values['json'] = json_stringify(json_data)

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

        # update item
        db.update_item(
            table_name = Table.NEST.value,
            where = [ f'srl={params.srl}' ],
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Complete update Nest.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
