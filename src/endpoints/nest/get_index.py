from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.string import convert_date
from src.libs.object import json_parse

async def get_index(params: types.GetIndex):
    print('PARAMS:', params)

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
        if total == 0: raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.NEST.value,
            fields = fields,
            where = where,
            limit={
                'size': params.size,
                'page': params.page,
            },
            order={
                'order': params.order,
                'sort': params.sort,
            },
            debug = True,
        )
        def transform_item(item: dict) -> dict:
            if 'created_at' in item:
                item['created_at'] = convert_date(item['created_at'])
            if 'json' in item:
                item['json'] = json_parse(item['json'])
            return item
        index = [transform_item(item) for item in index]

        # TODO: 이전 버전에서는 다음과 같이 추가기능이 있다.
        # TODO: - 아티클 갯수 가져오기
        # TODO: - 앱 이름 가져오기

        # set result
        result = output.success({
            'message': 'Success get nest index.',
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
