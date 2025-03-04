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
        # set where
        where = [
            f'and srl="{params.srl}"',
        ]

        # check item
        count = db.get_count(
            table_name = 'json',
            where = where,
        )
        if count <= 0: raise Exception('Item not found.', 204)

        # check category_srl
        if params.category_srl:
            count = db.get_count(
                table_name = 'category',
                where = [ f'srl={params.category_srl}' ]
            )
            if count <= 0: raise Exception('Category not found.', 455)

        # set values
        values = {}
        if params.category_srl:
            values['category_srl'] = params.category_srl
        if params.name:
            values['name'] = params.name
        if params.description:
            values['description'] = params.description
        if params.json_data:
            json_data = parse_json(params.json_data)
            values['json'] = json_stringify(json_data, None) or '{}'
        if params.path:
            check_url(params.path)
            values['path'] = params.path

        # check values
        if not bool(values):
            raise Exception('No values to update.', 422)

        # set placeholder
        placeholders = []
        if 'category_srl' in values and values['category_srl']:
            placeholders.append('category_srl = :category_srl')
        if 'name' in values and values['name']:
            placeholders.append('name = :name')
        if 'description' in values and values['description']:
            placeholders.append('description = :description')
        if 'json' in values and values['json']:
            placeholders.append('json = :json')
        if 'path' in values and values['path']:
            placeholders.append('path = :path')

        # update item
        db.edit_item(
            table_name = 'json',
            where = where,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success update JSON.',
        })
    except Exception as e:
        match e.args[1] if len(e.args) > 1 else 500:
            case 204:
                result = output.empty({
                    'message': e.args[0],
                })
            case _:
                result = output.error(None, {
                    'error': e,
                })
    finally:
        db.disconnect()
        return result
