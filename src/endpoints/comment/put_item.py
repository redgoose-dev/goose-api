from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def put_item(params: dict = {}, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PutItem(**params)

        # checking token
        checking_token(req, db)

        # check exist module item
        match params.module:
            case 'article': table_name = Table.ARTICLE.value
            case _: table_name = ''
        if not table_name: raise Exception('Module item not found.', 400)
        count = db.get_count(
            table_name = table_name,
            where = [ f'srl = {params.module_srl}' ],
        )
        if not (count > 0): raise Exception('Module item not found.', 400)

        # set values
        values = {
            'content': params.content,
            'module': params.module,
            'module_srl': params.module_srl,
        }

        # set placeholders
        placeholders = [
            { 'key': 'content', 'value': ':content' },
            { 'key': 'module', 'value': ':module' },
            { 'key': 'module_srl', 'value': ':module_srl' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add data
        data = db.add_item(
            table_name = Table.COMMENT.value,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Complete add comment.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
