from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

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

        # set data assets
        where = []
        values = {}
        join = []

        # set base params
        if params.content:
            where.append(f'and content LIKE "%{params.content}%"')
        if params.start and params.end:
            where.append(f'and (`created_at` BETWEEN "{params.start}" AND "{params.end}")')

        # set tag
        if params.tag:
            from ..tag import __libs__ as tag_lib
            _tag = ','.join(params.tag.split(','))
            where.append(f'AND checklist.srl IN (SELECT map_tag.module_srl FROM map_tag WHERE map_tag.module LIKE :module AND map_tag.tag_srl IN ({_tag}))')
            values['module'] = tag_lib.Module.CHECKLIST

        # get total
        total = db.get_count(
            table_name = Table.CHECKLIST.value,
            where = where,
            join = join,
            values = values,
        )
        if not (total > 0): raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.CHECKLIST.value,
            fields = fields,
            where = where,
            join = join,
            limit = {
                'size': params.size,
                'page': params.page,
            },
            order = {
                'order': params.order,
                'sort': params.sort,
            },
            values = values,
            unlimited = params.unlimited,
        )

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
