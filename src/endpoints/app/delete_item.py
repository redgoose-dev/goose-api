from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def delete_item(params: types.DeleteItem, _db: DB = None):

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

        # delete item
        db.delete_item(
            table_name = Table.APP.value,
            where = where,
        )

        # update nest
        db.update_item(
            table_name = Table.NEST.value,
            where = [ f'app_srl={params.srl}' ],
            placeholders = ['app_srl = :app_srl'],
            values = { 'app_srl': None },
        )

        # update article
        db.update_item(
            table_name = Table.ARTICLE.value,
            where = [ f'app_srl={params.srl}' ],
            placeholders = [ 'app_srl = :app_srl' ],
            values = { 'app_srl': None },
        )

        # set result
        result = output.success({
            'message': 'Success delete App.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
