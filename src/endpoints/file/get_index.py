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
        checking_token(req, db)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # ser where
        where = []
        if params.module and params.module_srl:
            where.append(f'and module LIKE "{params.module}"')
            where.append(f'and module_srl = {params.module_srl}')
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')
        if params.mime:
            where.append(f'and mime LIKE "%{params.mime}%"')

        # get total
        total = db.get_count(
            table_name = Table.FILE.value,
            where = where,
        )
        if not (total > 0): raise Exception('No data', 204)

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
            unlimited = params.unlimited,
        )
        def transform_item(item: dict) -> dict:
            if 'json' in item:
                item['json'] = json_parse(item['json'])
            return item
        index = [transform_item(item) for item in index]

        # set result
        result = output.success({
            'message': 'Complete get File index.',
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
