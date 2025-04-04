from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def get_login(req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # TODO: 로그인 페이지에서 사용할 준비 데이터 가져오기 (ready-login)
        # TODO: 작업예정

        # set result
        result = output.success({
            'message': 'Complete get login data.',
            'data': {},
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
