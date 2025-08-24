from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __types__ as types, __libs__ as checklist_libs

async def put_item(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PutItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # adjust content
        content = checklist_libs.filtering_content(params.content)

        # get percent into content
        percent = checklist_libs.get_percent_into_checkboxes(content)

        # set values
        values = {
            'content': params.content,
            'percent': percent,
            'regdate': params.regdate if params.regdate else None,
        }

        # set placeholders
        placeholders = [
            { 'key': 'content', 'value': ':content' },
            { 'key': 'percent', 'value': ':percent' },
            {
                'key': 'created_at',
                'value': ':regdate' if values['regdate'] else 'DATETIME("now", "localtime")',
            },
            {
                'key': 'updated_at',
                'value': ':regdate' if values['regdate'] else 'DATETIME("now", "localtime")',
            },
        ]

        # add data
        data = db.add_item(
            table_name=Table.CHECKLIST.value,
            placeholders=placeholders,
            values=values,
        )

        # add tag
        if params.tag:
            from ..tag import __libs__ as tag_libs
            tag_libs.add(
                _db=db,
                tags=params.tag,
                module=tag_libs.Module.CHECKLIST,
                module_srl=data,
            )

        # set result
        result = output.success({
            'message': 'Complete add checklist item.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
