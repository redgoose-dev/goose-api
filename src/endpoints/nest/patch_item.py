from . import __types__ as types
from src import output
from src.libs.db import DB
from src.libs.check import parse_json, check_url
from src.libs.object import json_stringify

async def patch_item(params: types.PatchItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # check parse json
        json_data = None
        if params.json_data: json_data = parse_json(params.json_data)

        # set where
        where = [ f'srl={params.srl}' ]

        # check item
        count = db.get_count(
            table_name = 'nest',
            where = where,
        )
        if count <= 0: raise Exception('Item not found.', 204)

        # check app_srl
        if params.app_srl:
            count = db.get_count(
                table_name = 'app',
                where = [ f'srl={params.app_srl}' ],
            )
            if count <= 0: raise Exception('Not found App', 409)

        # check code
        if params.code:
            count = db.get_count(
                table_name = 'nest',
                where = [ f'code="{params.code}"' ],
            )
            if count > 0: raise Exception('Exist code in Nest.', 409)

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
            values['json'] = json_stringify(json_data, None) or '{}'

        # check values
        if not bool(values):
            raise Exception('No values to update.', 422)

        # set placeholder
        placeholders = []
        if 'app_srl' in values and values['app_srl']:
            placeholders.append('app_srl = :app_srl')
        if 'code' in values and values['code']:
            placeholders.append('code = :code')
        if 'name' in values and values['name']:
            placeholders.append('name = :name')
        if 'description' in values and values['description']:
            placeholders.append('description = :description')
        if 'json' in values and values['json']:
            placeholders.append('json = :json')

        # update item
        db.edit_item(
            table_name = 'nest',
            where = where,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success update Nest.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
