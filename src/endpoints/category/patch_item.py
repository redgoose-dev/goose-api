from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__libs__ import check_module
from src.modules.verify import checking_token

async def patch_item(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchItem(**params)

        # checking token
        checking_token(req, db)

        # check item
        count = db.get_count(
            table_name = Table.CATEGORY.value,
            where = [ f'srl="{params.srl}"' ],
        )
        if count == 0: raise Exception('Item not found.', 204)

        # set values
        values = {}
        if params.name:
            values['name'] = params.name

        # check module
        if params.module:
            check_module(db, params.module, params.module_srl)
            values['module'] = params.module
            if params.module_srl: values['module_srl'] = params.module_srl
            # get max turn
            _where = [ f'and module LIKE "{params.module}"' ]
            if params.module_srl:
                _where.append(f'and module_srl = {params.module_srl}')
            max_number = db.get_max_number(
                table_name = Table.CATEGORY.value,
                field_name = 'turn',
                where = _where,
            )
            values['turn'] = max_number + 1

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

        # set placeholder
        placeholders = []
        if 'name' in values and values['name']:
            placeholders.append('name = :name')
        if 'module' in values and values['module']:
            placeholders.append('module = :module')
        if 'module_srl' in values and values['module_srl']:
            placeholders.append('module_srl = :module_srl')
        if 'turn' in values and values['turn']:
            placeholders.append('turn = :turn')

        # update item
        db.update_item(
            table_name = Table.CATEGORY.value,
            placeholders = placeholders,
            values = values,
            where = [ f'srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete update Category.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
