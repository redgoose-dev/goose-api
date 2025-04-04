from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from ..file import __libs__ as file_libs
from ..tag import __libs__ as tag_libs

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
            table_name = Table.JSON.value,
            where = where,
        )
        if count == 0: raise Exception('no data', 204)

        # delete item
        db.delete_item(
            table_name = Table.JSON.value,
            where = where,
        )

        # delete files
        file_libs.delete(db, file_libs.Module.JSON, params.srl)

        # delete tag
        tag_libs.delete(db, tag_libs.Module.JSON, params.srl)

        # set result
        result = output.success({
            'message': 'Complete delete JSON.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
