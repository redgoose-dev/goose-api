from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token
from src.modules.mod import MOD
from . import __types__ as types

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

        # MOD / count-file
        if mod.check('count-file'):
            count = db.get_count(
                table_name=Table.FILE.value,
                where=[
                    f'AND module=\'json\'',
                    f'AND module_srl = {params.srl}',
                ]
            )
            data['count_file'] = count or 0

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
