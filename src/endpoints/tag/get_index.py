from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def get_index(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetIndex(**params)

        print(params)

        # get total
        total = 0

        # get index
        index = []

        # set result
        result = output.success({
            'message': 'Complete get tag index.',
            'data': {
                'total': total,
                'index': index,
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
