from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token
from src.modules.mod import MOD
from ..category import __libs__ as category_libs

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
        if params.category_srl is not None:
            if params.category_srl > 0:
                where.append(f'and category_srl = {params.category_srl}')
            else:
                where.append(f'and category_srl IS NULL')
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')

        # set tag
        if params.tag:
            from ..tag import __libs__ as tag_lib
            _tag = ','.join(params.tag.split(','))
            where.append(f'AND json.srl IN (SELECT map_tag.module_srl FROM map_tag WHERE map_tag.module LIKE :module AND map_tag.tag_srl IN ({_tag}))')
            values['module'] = tag_lib.Module.JSON

        # get total
        total = db.get_count(
            table_name = Table.JSON.value,
            where = where,
            join = join,
            values = values,
        )
        if total == 0: raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.JSON.value,
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
        def transform_item(item: dict) -> dict:
            if 'json' in item:
                item['json'] = json_parse(item['json'])
            # MOD / category
            if mod.check('category'):
                item['category'] = category_libs.get_item(
                    _db = db,
                    srl = item.get('category_srl', 0),
                    fields = [ 'srl', 'name' ],
                )
            return item
        index = [ transform_item(item) for item in index ]

        # set result
        result = output.success({
            'message': 'Complete get JSON index.',
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
