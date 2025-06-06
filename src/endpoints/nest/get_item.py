from typing import Optional
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token
from src.modules.mod import MOD
from ..app import __libs__ as app_libs
from ..article import __libs__ as article_libs

async def get_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # set srl
        srl: Optional[int] = None
        code: Optional[str] = None
        try: srl = int(params.srl)
        except ValueError: code = str(params.srl)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set mod
        mod = MOD(params.mod or '')

        # set where
        where = []
        if srl: where.append(f'and srl = {srl}')
        if code: where.append(f'and code LIKE \'{code}\'')

        # get data
        data = db.get_item(
            table_name=Table.NEST.value,
            where=where,
            fields=fields,
        )
        if not data: raise Exception('Item not found', 204)
        if 'json' in data: data['json'] = json_parse(data.get('json'))

        # MOD / app
        if mod.check('app') and data.get('app_srl'):
            data['app'] = app_libs.get_item(
                _db=db,
                srl=data.get('app_srl'),
                fields=['srl', 'code', 'name'],
            )
        # MOD / count-article
        if mod.check('count-article') and data.get('srl'):
            data['count_article'] = article_libs.get_count(
                _db=db,
                where=[ f'nest_srl = {data['srl']}' ],
            )

        # set result
        result = output.success({
            'message': 'Complete get nest item.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
