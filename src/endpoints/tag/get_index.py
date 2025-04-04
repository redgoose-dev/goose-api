from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def get_index(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetIndex(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # set data assets
        fields = []
        where = []
        values = {}
        join = []

        # set base params
        if params.name:
            where.append(f'AND name LIKE "%{params.name}%"')
        if params.module and params.module_srl:
            fields = [ f'{Table.TAG.value}.*' ]
            join.append(f'JOIN {Table.MAP_TAG.value} ON {Table.MAP_TAG.value}.tag_srl = {Table.TAG.value}.srl')
            where.append(f'and module LIKE "{params.module}"')
            where.append(f'and module_srl = {params.module_srl}')

        # get total
        total = db.get_count(
            table_name=Table.TAG.value,
            where=where,
            join=join,
            values=values,
        )

        # get index
        index = db.get_items(
            table_name=Table.TAG.value,
            fields=fields,
            where=where,
            join=join,
            limit={
                'size': params.size,
                'page': params.page,
            },
            order={
                'order': params.order,
                'sort': params.sort,
            },
            values=values,
            unlimited=params.unlimited,
        )

        # set result
        result = output.success({
            'message': 'Complete get tag index.',
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
