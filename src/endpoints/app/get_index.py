from . import __types__ as types
from src import output
from src.libs.db import DB
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
        if params.id:
            where.append(f'and id="{params.id}"')
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')

        # get total
        total = db.get_count(
            table_name = 'app',
            where = where,
        )
        if total == 0: raise Exception('No data', 204)

        # set values
        values = {}

        # get index
        index = db.get_items(
            table_name = 'app',
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

        # TODO: 전 버전에서는 nest 데이터 갯수도 포함하는 옵션도 존재한다.

        # set result
        result = output.success({
            'message': 'Success get items index.',
            'data': {
                'total': total,
                'index': index,
            },
        })
    except Exception as e:
        match e.args[1] if len(e.args) > 1 else 500:
            case 204:
                result = output.empty({
                    'message': e.args[0],
                })
            case _:
                result = output.error(None, {
                    'error': e,
                })
    finally:
        db.disconnect()
        return result
