from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __types__ as types, __libs__ as file_libs

async def delete_item(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # set where
        where = [ f'srl = {params.srl}' ]

        # get item
        item = db.get_item(
            table_name=Table.FILE.value,
            fields=[ 'srl', 'code', 'path' ],
            where=where,
        )
        if not item: raise Exception('Item not found.', 204)

        # delete item
        db.delete_item(
            table_name=Table.FILE.value,
            where=where,
        )

        # delete file
        file_libs.delete_file(item['path'])

        # delete cache files
        file_libs.delete_cache_files(item.get('code'))

        # set result
        result = output.success({
            'message': 'Complete delete file.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
