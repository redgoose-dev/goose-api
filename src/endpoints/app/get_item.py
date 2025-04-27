from typing import Optional
from src import output
from src.libs.db import DB, Table
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
        if srl: where.append(f'and srl={srl}')
        if code: where.append(f'and code LIKE \'{code}\'')

        # get item
        data = db.get_item(
            table_name=Table.APP.value,
            where=where,
            fields=fields,
        )
        if not data: raise Exception('Item not found', 204)

        # MOD / count-nest
        if mod.check('count-nest'):
            from ..nest import __libs__ as nest_libs
            data['count_nest'] = nest_libs.get_count(db, [ f'app_srl = {data['srl']}' ])

        # MOD / count-article
        if mod.check('count-article'):
            from ..article import __libs__ as article_libs
            data['count_article'] = article_libs.get_count(db, [ f'app_srl = {data['srl']}' ])

        # set result
        result = output.success({
            'message': 'Complete get app item.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
