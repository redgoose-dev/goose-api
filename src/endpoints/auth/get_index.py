from . import __types__ as types
from src import output
from src.libs.db import DB, Table
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
        def transform_item(item: dict) -> dict:
            if 'user_password' in item: del item['user_password']
            return item
        index = [transform_item(item) for item in index]

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
        if db: db.disconnect()
        return result
