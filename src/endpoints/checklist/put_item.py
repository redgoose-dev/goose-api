from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__libs__ import filtering_content, get_percent_into_checkboxes

async def put_item(params: types.PutItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
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
        if not _db: db.disconnect()
        return result
