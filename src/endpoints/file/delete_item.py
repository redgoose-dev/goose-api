from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from .__lib__ import delete_file

async def delete_item(params: types.DeleteItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        checking_token(req, db)

        # set where
        where = [ f'srl = {params.srl}' ]

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
            'message': 'Complete delete file.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
