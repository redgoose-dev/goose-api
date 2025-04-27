from . import __types__ as types
from src import output
from src.libs.db import DB, Table
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
        if not bool(values): raise Exception('No values to update.', 400)

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
                where = [ f'code LIKE :code' ],
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
            'message': 'Complete update app.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
