from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token

async def get_index(params: types.GetIndex, req = None, _db: DB = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # checking token
        checking_token(req, db)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set where
        where = []
        if params.app_srl:
            where.append(f'and app_srl={params.app_srl}')
        if params.code:
            where.append(f'and code LIKE "{params.code}"')
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')

        # get total
        total = db.get_count(
            table_name = Table.NEST.value,
            where = where,
        )
        if not (total > 0): raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.NEST.value,
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
            unlimited = params.unlimited,
        )
        def transform_item(item: dict) -> dict:
            if 'json' in item and item['json']:
                item['json'] = json_parse(item['json'])
            return item
        index = [transform_item(item) for item in index]

        # TODO: mod - 아티클 갯수 가져오기
        # TODO: mod - 앱 이름 가져오기

        # set result
        result = output.success({
            'message': 'Complete get nest index.',
            'data': {
                'total': total,
                'index': index,
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
