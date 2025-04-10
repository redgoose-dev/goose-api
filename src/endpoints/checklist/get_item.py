from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from ..tag import __libs__ as tag_libs

async def get_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # get data
        data = db.get_item(
            table_name = Table.CHECKLIST.value,
            fields = fields,
            where = [ f'srl = {params.srl}' ],
        )
        if not data: raise Exception('Item not found', 204)

        # get tag
        tag_index = tag_libs.get_index(
            _db = db,
            module = tag_libs.Module.CHECKLIST,
            module_srl = params.srl,
        )
        if tag_index and len(tag_index) > 0: data['tag'] = tag_index

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
