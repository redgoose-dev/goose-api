from typing import Optional
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse

async def get_item(params: types.GetItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # set srl
        srl: Optional[int] = None
        code: Optional[str] = None
        try: srl = int(params.srl)
        except ValueError: code = str(params.srl)

        # set where
        where = []
        if srl: where.append(f'and srl={srl}')
        if code: where.append(f'and code LIKE "{code}"')

        # get data
        data = db.get_item(
            table_name = Table.NEST.value,
            where = where,
        )
        if not data: raise Exception('Item not found', 204)
        data['json'] = json_parse(data['json'])

        # set result
        result = output.success({
            'message': 'Complete get Nest item.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
