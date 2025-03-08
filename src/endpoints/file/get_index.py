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
        # set fields
        fields = params.fields.split(',') if params.fields else None

        # ser where
        where = []
        if params.module and params.target_srl:
            where.append(f'and module LIKE "{params.module}"')
            where.append(f'and target_srl={params.target_srl}')
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')
        if params.type:
            where.append(f'and type LIKE "%{params.type}%"')

        # get total
        total = db.get_count(
            table_name = Table.FILE.value,
            where = where,
        )
        if total == 0: raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.FILE.value,
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
            if 'json' in item:
                item['json'] = json_parse(item['json'])
            if 'created_at' in item:
                item['created_at'] = convert_date(item['created_at'])
            return item
        index = [transform_item(item) for item in index]

        # set result
        result = output.success({
            'message': 'Get index.',
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
