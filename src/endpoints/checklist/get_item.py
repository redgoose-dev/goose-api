from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from src.modules.mod import MOD
from . import __types__ as types
from ..tag import __libs__ as tag_libs

async def get_item(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set mod
        mod = MOD(params.mod or '')

        # get data
        data = db.get_item(
            table_name=Table.CHECKLIST.value,
            fields=fields,
            where=[ f'srl = {params.srl}' ],
        )
        if not data: raise Exception('Item not found', 204)

        # MOD / count-file
        if mod.check('count-file'):
            count = db.get_count(
                table_name=Table.FILE.value,
                where=[
                    f'AND module=\'checklist\'',
                    f'AND module_srl = {params.srl}',
                ]
            )
            data['count_file'] = count or 0

        # MOD / tag
        if mod.check('tag'):
            _tags = tag_libs.get_index(
                _db=db,
                module=tag_libs.Module.CHECKLIST,
                module_srl=params.srl,
            )
            data['tag'] = _tags if _tags and len(_tags) > 0 else None

        # set result
        result = output.success({
            'message': 'Complete get checklist item.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
