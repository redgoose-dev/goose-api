from . import __types__ as types
from src import output
from src.libs.db import DB, Table

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

        # check item
        count = db.get_count(
            table_name = Table.JSON.value,
            where = where,
        )
        if count == 0: raise Exception('Item not found.', 204)

        # delete item
        db.delete_item(
            table_name = Table.JSON.value,
            where = where,
        )

        # set result
        result = output.success({
            'message': 'Success delete JSON.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
