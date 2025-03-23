from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from .__libs__ import filtering_content, get_percent_into_checkboxes

async def put_item(params: types.PutItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        db = checking_token(req, db)

        # adjust content
        content = filtering_content(params.content)

        # get percent into content
        percent = get_percent_into_checkboxes(content)

        # set values
        values = {
            'content': params.content,
            'percent': percent,
        }

        # set placeholders
        placeholders = [
            { 'key': 'content', 'value': ':content' },
            { 'key': 'percent', 'value': ':percent' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add item
        data = db.add_item(
            table_name = Table.CHECKLIST.value,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Complete add checklist item.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
