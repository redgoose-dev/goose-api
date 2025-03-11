from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.string import convert_date

async def get_item(params: types.GetItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    print('PARAMS: ', params)

    try:
        # TODO: 인증 검사하기
        pass
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
