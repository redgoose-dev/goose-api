from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __libs__ as comment_libs

async def patch_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check data
        count = db.get_count(
            table_name=Table.COMMENT.value,
            where=[ f'srl = {params.srl}' ],
        )
        if count == 0: raise Exception('Data not found.', 204)

        # set values
        values = {}
        if params.module or params.module_srl:
            comment_libs.check_module(db, params.module, params.module_srl)
            values['module'] = params.module
            values['module_srl'] = params.module_srl
        if params.content:
            values['content'] = params.content

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

        # set placeholder
        placeholders = []
        if 'module' in values and values['module']:
            placeholders.append('module = :module')
        if 'module_srl' in values and values['module_srl']:
            placeholders.append('module_srl = :module_srl')
        if 'content' in values and values['content']:
            placeholders.append('content = :content')
        placeholders.append('updated_at = DATETIME("now", "localtime")')

        # update data
        db.update_item(
            table_name=Table.COMMENT.value,
            placeholders=placeholders,
            values=values,
            where=[ f'srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete update comment.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
