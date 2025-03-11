from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def patch_item(params: types.PatchItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # TODO: 인증 검사하기

        # set where
        where = [ f'and srl={params.srl}' ]

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
            raise Exception('No values to update.', 400)

        # set sets
        placeholders = []
        if 'code' in values:
            placeholders.append('code = :code')
        if 'name' in values:
            placeholders.append('name = :name')
        if 'description' in values:
            placeholders.append('description = :description')

        # check exist code
        if 'code' in values and values['code']:
            count = db.get_count(
                table_name = Table.APP.value,
                where = [ f'and code LIKE :code' ],
                values = { 'code': values['code'] },
            )
            if count > 0: raise Exception('"code" already exists.')

        # update item
        db.update_item(
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
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
