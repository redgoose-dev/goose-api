from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def post_logout(req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # checking token
        if _check_token: checking_token(req, db)

        # get authorization
        authorization = req.headers.get('authorization')

        # get data
        count = db.get_count(
            table_name = Table.TOKEN.value,
            where = [ f'access LIKE "{authorization}"' ],
        )
        if not (count > 0): raise Exception('Token not found.', 204)

        # delete token
        db.delete_item(
            table_name = Table.TOKEN.value,
            where = [ f'access LIKE "{authorization}"' ],
        )

        # set result
        result = output.success({
            'message': 'Complete logout.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
