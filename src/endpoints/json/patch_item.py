from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.check import check_url
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
        count = db.get_count(
            table_name=Table.JSON.value,
            where=[ f'srl = {params.srl}' ],
        )
        if count <= 0: raise Exception('Item not found.', 204)

        # set values
        values = {}
        if params.name:
            values['name'] = params.name
        if params.description:
            values['description'] = params.description
        if params.json_data:
            json_data = json_parse(params.json_data)
            values['json'] = json_stringify(json_data, None) or '{}'

        # check category_srl
        if params.category_srl:
            from ..category import __libs__ as category_libs
            count = db.get_count(
                table_name=Table.CATEGORY.value,
                where=[
                    f'AND module LIKE \'{category_libs.Module.JSON}\'',
                    f'AND srl = {params.category_srl}',
                ]
            )
            if not (count > 0): raise Exception('Invalid category_srl', 400)
            values['category_srl'] = params.category_srl
        elif params.category_srl is not None:
            values['category_srl'] = 0

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

        # set placeholder
        placeholders = []
        if 'category_srl' in values:
            placeholders.append(f'category_srl = {':category_srl' if values['category_srl'] > 0 else 'NULL'}')
        if 'name' in values and values['name']:
            placeholders.append('name = :name')
        if 'description' in values and values['description']:
            placeholders.append('description = :description')
        if 'json' in values and values['json']:
            placeholders.append('json = :json')
        placeholders.append('updated_at = DATETIME("now", "localtime")')

        # update item
        db.update_item(
            table_name=Table.JSON.value,
            placeholders=placeholders,
            values=values,
            where=[ f'srl = {params.srl}' ],
        )

        # update tag
        if params.tag:
            from ..tag import __libs__ as tag_libs
            tag_libs.update(
                _db=db,
                new_tags=params.tag,
                module=tag_libs.Module.JSON,
                module_srl=params.srl,
            )

        # set result
        result = output.success({
            'message': 'Complete update JSON.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
