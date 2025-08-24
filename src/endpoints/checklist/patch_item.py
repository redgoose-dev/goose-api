from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __types__ as types, __libs__ as checklist_libs

async def patch_item(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # adjust content
        if params.content:
            content = checklist_libs.filtering_content(params.content)
            percent = checklist_libs.get_percent_into_checkboxes(content)
        else:
            content = None
            percent = None

        # set values
        values = {}
        if content is not None:
            values['content'] = content
        if percent is not None:
            values['percent'] = percent

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

        # set placeholders
        placeholders = []
        if 'content' in values:
            placeholders.append('content = :content')
        if 'percent' in values:
            placeholders.append('percent = :percent')
        placeholders.append('updated_at = DATETIME("now", "localtime")')

        # add item
        db.update_item(
            table_name=Table.CHECKLIST.value,
            where=[ f'srl = {params.srl}' ],
            placeholders=placeholders,
            values=values,
        )

        # update tag
        if params.tag:
            from ..tag import __libs__ as tag_libs
            tag_libs.update(
                _db=db,
                module=tag_libs.Module.CHECKLIST,
                module_srl=params.srl,
                new_tags=params.tag,
            )

        # set result
        result = output.success({
            'message': 'Complete update checklist item.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
