from . import types
from ...output import success, error
from ...libs.db import DB

async def add_item(params: types.AddItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # TO`DO: id 검사해야함
        # count = db.get_count()
        # data = db.get_item(
        #     table_name = 'app',
        #     where = 'srl=1',
        # )`
        data = db.add_item(
            table_name = 'app',
            data = [
                { 'key': 'id', 'value': params.id },
                { 'key': 'name', 'value': params.name },
                { 'key': 'description', 'value': params.description },
                { 'key': 'created_at', 'key_name': 'CURRENT_TIMESTAMP' },
            ]
        )
        result = success({
            'message': 'app-add-item',
            'params': params.model_dump(),
            'data': data,
        })
    except Exception as e:
        result = error(None, {
            'error': e,
        })
    finally:
        # disconnect db
        db.disconnect()
        # return
        return result
