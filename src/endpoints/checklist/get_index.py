from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def get_index(params: types.GetIndex, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set where
        where = []
        if params.content:
            where.append(f'and content LIKE "%{params.content}%"')
        if params.start and params.end:
            where.append(f'and (`created_at` BETWEEN "{params.start}" AND "{params.end}")')

        # get total
        total = db.get_count(
            table_name = Table.CHECKLIST.value,
            where = where,
        )
        if not (total > 0): raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.CHECKLIST.value,
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

        # set result
        result = output.success({
            'message': 'Complete get checklist index.',
            'data': {
                'total': total,
                'index': index,
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
