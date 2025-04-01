from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def put_item(req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # checking token
        if _check_token: checking_token(req, db)

        # check ready mode item
        item = db.get_item(
            table_name = Table.ARTICLE.value,
            fields = [ 'srl' ],
            where = [ f'mode LIKE "ready"' ],
        )
        if item:
            data = item['srl']
        else:
            data = db.add_item(
                table_name = Table.ARTICLE.value,
                placeholders = [ { 'key': 'mode', 'value': ':mode' } ],
                values = { 'mode': 'ready' },
            )

        # set result
        result = output.success({
            'message': 'Complete add article.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
