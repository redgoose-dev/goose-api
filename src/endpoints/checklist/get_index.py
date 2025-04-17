from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from src.modules.mod import MOD
from . import __types__ as types
from ..tag import __libs__ as tag_libs

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

        # set data assets
        where = []
        values = {}
        join = []

        # set base params
        if params.content:
            where.append(f'and content LIKE "%{params.content}%"')
        if params.start and params.end:
            where.append(f'and (`created_at` BETWEEN "{params.start} 00:00:00" AND "{params.end} 23:59:59")')

        # set tag
        if params.tag:
            from ..tag import __libs__ as tag_lib
            _tags = ','.join(params.tag.split(','))
            where.append(f'AND checklist.srl IN (SELECT map_tag.module_srl FROM map_tag WHERE map_tag.module LIKE :module AND map_tag.tag_srl IN ({_tags}))')
            values['module'] = tag_lib.Module.CHECKLIST

        # get total
        total = db.get_count(
            table_name=Table.CHECKLIST.value,
            where=where,
            join=join,
            values=values,
        )
        if not (total > 0): raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name=Table.CHECKLIST.value,
            fields=fields,
            where=where,
            join=join,
            limit={ 'size': params.size, 'page': params.page },
            order={ 'order': params.order, 'sort': params.sort },
            values=values,
            unlimited=params.unlimited,
        )
        def transform_item(item: dict) -> dict:
            # MOD / tag
            if mod.check('tag'):
                item['tag'] = tag_libs.get_index(
                    _db=db,
                    module=tag_libs.Module.CHECKLIST,
                    module_srl=item.get('srl'),
                )
            return item
        index = [ transform_item(item) for item in index ]

        # set result
        result = output.success({
            'message': 'Complete get checklist index.',
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
