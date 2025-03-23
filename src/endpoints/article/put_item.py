from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def put_item(params: types.PutItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        db = checking_token(req, db)

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
            'message': 'Success add Article.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
