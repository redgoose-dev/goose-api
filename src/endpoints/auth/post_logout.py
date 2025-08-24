from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def post_logout(req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # checking token
        token = checking_token(req, db) if not _token else _token

        # get authorization
        authorization = req.headers.get('authorization')

        # update expires to 0
        db.update_item(
            table_name=Table.TOKEN.value,
            placeholders=[ 'expires = :expires' ],
            values={ 'expires': 0 },
            where=[ f'access LIKE \'{authorization}\'' ],
        )

        # set result
        result = output.success({
            'message': 'Complete logout.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
