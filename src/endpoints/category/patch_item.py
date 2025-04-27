from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __libs__ as category_libs

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
        item = db.get_item(
            table_name=Table.CATEGORY.value,
            where=[ f'srl = {params.srl}' ],
        )
        if not item: raise Exception('Item not found.', 204)

        # set values
        values = {}
        if params.name:
            values['name'] = params.name

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

        # set placeholder
        placeholders = []
        if 'name' in values and values['name']:
            placeholders.append('name = :name')

        # update item
        db.update_item(
            table_name=Table.CATEGORY.value,
            placeholders=placeholders,
            values=values,
            where=[ f'srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete update category.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
