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
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')
        if params.module:
            where.append(f'and module="{params.module}"')
        if params.target_srl is not None:
            where.append(f'and target_srl={params.target_srl}')

        # get total
        total = db.get_count(
            table_name = 'category',
            where = where,
        )
        if total == 0: raise Exception('No data', 204)

        # set values
        values = {}

        # get index
        index = db.get_items(
            table_name = 'category',
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
            if 'created_at' in item:
                item['created_at'] = convert_date(item['created_at'])
            return item
        index = [transform_item(item) for item in index]

        # set result
        result = output.success({
            'message': 'Success get category index.',
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
