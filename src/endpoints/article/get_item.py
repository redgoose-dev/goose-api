from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token
from src.modules.mod import MOD

async def get_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set base values
        params = types.GetItem(**params)
        placeholder = []

        # checking token
        if _check_token: checking_token(req, db)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set mod
        mod = MOD(params.mod or '')

        # set data
        data = db.get_item(
            table_name = Table.ARTICLE.value,
            fields = fields,
            where = [ f'and srl = {params.srl}' ],
        )
        if not data: raise Exception('no data', 204)
        if data and isinstance(data, dict):
            if 'json' in data: data['json'] = json_parse(data['json'])

        # MOD / hit or star
        if mod.check('up-hit') or mod.check('up-star'):
            placeholder = []
            if mod.check('up-hit'): placeholder.append('hit = hit + 1')
            if mod.check('up-star'): placeholder.append('star = star + 1')

        # update data
        if len(placeholder):
            db.update_item(
                table_name = Table.ARTICLE.value,
                where = [ f'srl = {params.srl}' ],
                placeholders = placeholder,
            )

        # set result
        result = output.success({
            'message': 'Complete get article item.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
