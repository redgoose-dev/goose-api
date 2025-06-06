from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __libs__ as comment_libs

async def put_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PutItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check exist module item
        match params.module:
            case comment_libs.Module.ARTICLE: table_name = Table.ARTICLE.value
            case _: table_name = None
        if not table_name: raise Exception('Invalid module name.', 400)
        count = db.get_count(
            table_name=table_name,
            where=[ f'srl = {params.module_srl}' ],
        )
        if not (count > 0): raise Exception('Module data not found.', 400)

        # set values
        values = {
            'module': params.module,
            'module_srl': params.module_srl,
            'content': params.content,
        }

        # set placeholders
        placeholders = [
            { 'key': 'module', 'value': ':module' },
            { 'key': 'module_srl', 'value': ':module_srl' },
            { 'key': 'content', 'value': ':content' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
            { 'key': 'updated_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add data
        data = db.add_item(
            table_name=Table.COMMENT.value,
            placeholders=placeholders,
            values=values,
        )

        # set result
        result = output.success({
            'message': 'Complete add comment.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
