from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__libs__ import check_module
from src.modules.verify import checking_token

async def put_item(params: types.PutItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        checking_token(req, db)

        # check module
        check_module(db, params.module, params.module_srl)

        # check module and get max turn
        where = [ f'and module LIKE "{params.module}"' ]
        if params.module_srl:
            where.append(f'and module_srl = {params.module_srl}')
        count = db.get_count(
            table_name = Table.CATEGORY.value,
            where = where,
        )

        # set values
        values = {
            'name': params.name,
            'module': params.module,
            'turn': count + 1,
        }
        if params.module_srl:
            values['module_srl'] = params.module_srl

        # set placeholders
        placeholders = [
            { 'key': 'name', 'value': ':name' },
            { 'key': 'module', 'value': ':module' },
        ]
        if 'module_srl' in values:
            placeholders.append({ 'key': 'module_srl', 'value': ':module_srl' })
        placeholders.append({ 'key': 'turn', 'value': ':turn' })
        placeholders.append({ 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' })

        # add item
        data = db.add_item(
            table_name = Table.CATEGORY.value,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Complete add Category.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
