from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def delete_item(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        checking_token(req, db)

        # set where
        where = [ f'srl = {params.srl}' ]

        # check item
        count = db.get_count(
            table_name = Table.CATEGORY.value,
            where = where,
        )
        if count == 0: raise Exception('Item not found.', 204)

        # delete item
        db.delete_item(
            table_name = Table.CATEGORY.value,
            where = where,
        )

        # set result
        result = output.success({
            'message': 'Complete delete category.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
