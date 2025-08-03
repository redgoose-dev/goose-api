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

        # set where
        where = [ f'AND expires IS NULL' ]
        if params.token: where.append(f'AND access LIKE :access')

        # set values
        values = {}
        if params.token: values['access'] = params.token

        # get total
        total = db.get_count(
            table_name=Table.TOKEN.value,
            where=where,
            values=values,
        )

        # get data
        index = db.get_items(
            table_name=Table.TOKEN.value,
            where=where,
            values=values,
        )

        # transform items
        def transform_item(item: dict) -> dict:
            return {
                'srl': item['srl'],
                'provider_srl': item['provider_srl'],
                'access': item['access'],
                'description': item['description'],
                'created_at': item['created_at'],
            }
        index = [transform_item(item) for item in index]

        # set result
        result = output.success({
            'message': 'Complete get public tokens.',
            'data': {
                'total': total,
                'index': index,
            },
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
