from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token

async def get_index(params: types.GetIndex, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        db = checking_token(req, db)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set where
        where = []
        if params.category_srl is not None:
            if params.category_srl == 0:
                where.append(f'and category_srl IS NULL')
            else:
                where.append(f'and category_srl = {params.category_srl}')
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')

        # set values
        values = {}

        # get total
        total = db.get_count(
            table_name = Table.JSON.value,
            where = where,
        )
        if total == 0: raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.JSON.value,
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
            values = values,
            unlimited = params.unlimited,
        )
        def transform_item(item: dict) -> dict:
            if 'json' in item:
                item['json'] = json_parse(item['json'])
            return item
        index = [ transform_item(item) for item in index ]

        # TODO: 전 버전에서는 다음과 같이 추가기능이 있다.
        # TODO: - 카테고리 목록 가져오기

        # set result
        result = output.success({
            'message': 'Complete get JSON index.',
            'data': {
                'total': total,
                'index': index,
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
