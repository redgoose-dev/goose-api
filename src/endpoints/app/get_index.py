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
        # set fields
        fields = params.fields.split(',') if params.fields else None

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

        # set values
        values = {}

        # get index
        index = db.get_items(
            table_name = Table.APP.value,
            fields = fields,
            where = where,
            values = values,
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
            return {
                **item,
                'created_at': convert_date(item['created_at']),
            }
        index = [ transform_item(item) for item in index ]

        # TODO: 이전 버전에서는 nest 데이터 갯수도 포함

        # set result
        result = output.success({
            'message': 'Success get items index.',
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
