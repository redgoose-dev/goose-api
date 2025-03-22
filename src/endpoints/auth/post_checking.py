from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def post_checking(params: types.PostChecking, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        result = output.success({
            'message': 'checking',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
