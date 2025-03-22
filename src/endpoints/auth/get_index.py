from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def get_index(params: types.GetIndex, _db: DB = None):

    # TODO: 테스트 해야함

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

        # get total
        total = db.get_count(
            table_name = Table.PROVIDER.value,
            where = where,
        )
        if not (total > 0): raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.PROVIDER.value,
            fields = fields,
            where = where,
            unlimited = True,
        )

        # set result
        result = output.success({
            'message': 'Complete get provider index.',
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
