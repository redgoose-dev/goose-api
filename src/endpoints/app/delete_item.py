from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def delete_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # set where
        where = [ f'srl = {params.srl}' ]

        # check item
        count = db.get_count(
            table_name = Table.APP.value,
            where = where,
        )
        if count == 0: raise Exception('Item not found.', 204)

        # delete item
        db.delete_item(
            table_name = Table.APP.value,
            where = where,
        )

        # update nest
        db.update_item(
            table_name = Table.NEST.value,
            where = [ f'app_srl = {params.srl}' ],
            placeholders = ['app_srl = :app_srl'],
            values = { 'app_srl': None },
        )

        # update article
        db.update_item(
            table_name = Table.ARTICLE.value,
            where = [ f'app_srl = {params.srl}' ],
            placeholders = [ 'app_srl = :app_srl' ],
            values = { 'app_srl': None },
        )

        # set result
        result = output.success({
            'message': 'Success delete app.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
