from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__lib__ import delete_file

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

        # get item
        item = db.get_item(
            table_name = Table.FILE.value,
            fields = [ 'srl', 'path' ],
            where = where,
        )
        if not item: raise Exception('Item not found.', 204)

        # delete item
        db.delete_item(
            table_name = Table.FILE.value,
            where = where,
        )

        # delete file
        delete_file(item['path'])

        # set result
        result = output.success({
            'message': 'Success delete File.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
