from . import __types__ as types
from src import output
from src.libs.db import DB
from src.libs.string import convert_date

async def get_item(params: types.GetItem):
    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # set where
        where = []
        if params.srl: where.append(f'and srl="{params.srl}"')

        # get item
        data = db.get_item(
            table_name = 'app',
            where = where,
        )
        if not data: raise Exception('Item not found', 204)
        data['created_at'] = convert_date(data['created_at'])

        # set result
        result = output.success({
            'message': 'Success get App item.',
            'data': data,
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
