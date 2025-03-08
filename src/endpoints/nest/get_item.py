from typing import Optional
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.string import convert_date
from src.libs.object import json_parse

async def get_item(params: types.GetItem):
    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

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
        data['created_at'] = convert_date(data['created_at'])
        data['json'] = json_parse(data['json'])

        # set result
        result = output.success({
            'message': 'Success get Nest item.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
