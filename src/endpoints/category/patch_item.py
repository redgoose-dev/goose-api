from . import __types__ as types
from src import output
from src.libs.db import DB, Table

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
            table_name = Table.CATEGORY.value,
            where = where,
        )
        if count == 0: raise Exception('Item not found.', 204)

        # set values
        values = {}
        if params.target_srl:
            values['target_srl'] = params.target_srl
        if params.name:
            values['name'] = params.name
        if params.module:
            values['module'] = params.module

        # check values
        if not bool(values):
            raise Exception('No values to update.', 422)

        # set placeholder
        placeholders = []
        if 'target_srl' in values and values['target_srl']:
            placeholders.append('target_srl = :target_srl')
        if 'name' in values and values['name']:
            placeholders.append('name = :name')
        if 'module' in values and values['module']:
            placeholders.append('module = :module')

        # update item
        db.edit_item(
            table_name = Table.CATEGORY.value,
            placeholders = placeholders,
            values = values,
            where = where,
        )

        # set result
        result = output.success({
            'message': 'Success update Category.',
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
