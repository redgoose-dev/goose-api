from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from src.modules.mod import MOD
from ..nest import __libs__ as nest_libs
from ..article import __libs__ as article_libs

async def get_index(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetIndex(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set mod
        mod = MOD(params.mod or '')

        # set where
        where = []
        if params.code:
            where.append(f'and code LIKE "{params.code}"')
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')

        # get total
        total = db.get_count(
            table_name = Table.APP.value,
            where = where,
        )
        if total == 0: raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.APP.value,
            fields = fields,
            where = where,
            limit = {
                'size': params.size,
                'page': params.page,
            },
            order = {
                'order': params.order,
                'sort': params.sort,
            },
        )

        # transform items
        def transform_item(item: dict) -> dict:
            # MOD / count-nest
            if mod.check('count-nest'):
                item['count_nest'] = nest_libs.get_count(db, [ f'app_srl = {item['srl']}' ])
            # MOD / count-article
            if mod.check('count-article'):
                item['count_article'] = article_libs.get_count(db, [ f'app_srl = {item['srl']}' ])
            return item
        index = [ transform_item(item) for item in index ]

        # set result
        result = output.success({
            'message': 'Complete get app index.',
            'data': {
                'total': total,
                'index': index,
            },
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
