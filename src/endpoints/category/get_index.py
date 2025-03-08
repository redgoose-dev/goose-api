from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.string import convert_date

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
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')
        if params.module:
            where.append(f'and module="{params.module}"')
        if params.target_srl is not None:
            where.append(f'and target_srl={params.target_srl}')

        # get total
        total = db.get_count(
            table_name = Table.CATEGORY.value,
            where = where,
        )
        if total == 0: raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.CATEGORY.value,
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
            return item
        index = [transform_item(item) for item in index]

        # TODO: 이전 버전에 있는 기능
        # TODO: - article,json 갯수 가져오기
        # TODO: - 카테고리 타입 (all,none) 값 가져오기

        # set result
        result = output.success({
            'message': 'Success get category index.',
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
