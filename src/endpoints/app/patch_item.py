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
        where = [ f'and srl="{params.srl}"' ]

        # check item
        count = db.get_count(
            table_name = Table.APP.value,
            where = where,
        )
        if count == 0: raise Exception('Item not found.', 204)

        # set values
        values = {}
        if params.code:
            values['code'] = params.code
        if params.name:
            values['name'] = params.name
        if params.description:
            values['description'] = params.description

        # check values
        if not bool(values):
            raise Exception('No values to update.', 422)

        # set sets
        placeholders = []
        if 'code' in values and values['code']:
            placeholders.append('code = :code')
        if 'name' in values and values['name']:
            placeholders.append('name = :name')
        if 'description' in values and values['description']:
            placeholders.append('description = :description')

        # check exist code
        if 'code' in values and values['code']:
            count = db.get_count(
                table_name = Table.APP.value,
                where = [ f'and code LIKE "{values['code']}"' ],
            )
            if count > 0: raise Exception('"code" already exists.')

        # update item
        db.edit_item(
            table_name = Table.APP.value,
            where = where,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success update App.',
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
