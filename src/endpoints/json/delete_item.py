from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def delete_item(params: types.DeleteItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        checking_token(req, db)

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

        # TODO: 파일 삭제하기 (이 데이터의 자식이기 때문)

        # set result
        result = output.success({
            'message': 'Complete delete JSON.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
