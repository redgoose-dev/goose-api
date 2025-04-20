from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token
from src.modules.mod import MOD
from . import __types__ as types
from ..tag import __libs__ as tag_libs
from ..category import __libs__ as category_libs

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

        # set mod
        mod = MOD(params.mod or '')

        # get data
        data = db.get_item(
            table_name=Table.JSON.value,
            fields=fields,
            where=[ f'srl = {params.srl}' ],
        )
        if not data: raise Exception('no data', 204)
        if isinstance(data, dict):
            if 'json' in data: data['json'] = json_parse(data['json'])

        # MOD / tag
        if mod.check('tag'):
            tag_index = tag_libs.get_index(
                _db=db,
                module=tag_libs.Module.JSON,
                module_srl=params.srl,
            )
            data['tag'] = tag_index if tag_index and len(tag_index) > 0 else None

        # MOD / category
        if mod.check('category'):
            data['category'] = category_libs.get_item(
                _db=db,
                srl=data.get('category_srl', 0),
                fields=[ 'srl', 'name' ],
            )

        # set result
        result = output.success({
            'message': 'Complete get JSON item.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
