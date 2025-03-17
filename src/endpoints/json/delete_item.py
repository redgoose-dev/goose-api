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
        # set where
        where = [ f'and srl="{params.srl}"' ]

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

        # TODO: 파일 삭제하기 (이 데이터의 자식이기 때문)

        # set result
        result = output.success({
            'message': 'Complete delete JSON.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
