from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.string import convert_date
from src.libs.object import json_parse

async def get_index(params: types.GetIndex):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # TODO: 인증 검사하기

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set where
        where = []
        if params.category_srl is not None:
            if params.category_srl == 0:
                where.append(f'and category_srl IS NULL')
            else:
                where.append(f'and category_srl={params.category_srl}')
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')

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
        )
        def transform_item(item: dict) -> dict:
            if 'created_at' in item:
                item['created_at'] = convert_date(item['created_at'])
            if 'json' in item:
                item['json'] = json_parse(item['json'])
            return item
        index = [ transform_item(item) for item in index ]

        # TODO: 전 버전에서는 다음과 같이 추가기능이 있다.
        # TODO: - 카테고리 목록 가져오기
        # TODO: 전 버전은 ext_field 로 추가 기능을 사용해는데 이번에는 mod 로 해도 좋을거 같다. 좀 짧게..

        # set result
        result = output.success({
            'message': 'Success get JSON index.',
            'data': {
                'total': total,
                'index': index,
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
