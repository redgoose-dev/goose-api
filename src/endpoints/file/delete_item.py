from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__lib__ import delete_file

async def delete_item(params: types.DeleteItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # set where
        where = []
        if params.srl: where.append(f'and srl="{params.srl}"')

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
        db.disconnect()
        return result
