from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__libs__ import check_module

async def patch_item(params: types.PatchItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    print('PARAMS:', params)

    try:
        # check item
        count = db.get_count(
            table_name = Table.COMMENT.value,
            where = [ f'srl="{params.srl}"' ],
        )
        if count == 0: raise Exception('Item not found.', 204)

        # set values
        values = {}
        if params.content:
            values['content'] = params.content
        if params.module and params.module_srl:
            check_module(db, params.module, params.module_srl)
            values['module'] = params.module
            values['module_srl'] = params.module_srl

        # check values
        if not bool(values):
            raise Exception('No values to update.', 400)

        # set placeholder
        placeholders = []
        if 'content' in values and values['content']:
            placeholders.append('content = :content')
        if 'module' in values and values['module']:
            placeholders.append('module = :module')
        if 'module_srl' in values and values['module_srl']:
            placeholders.append('module_srl = :module_srl')

        # update item
        db.update_item(
            table_name = Table.COMMENT.value,
            placeholders = placeholders,
            values = values,
            where = [ f'srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete update Comment.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
