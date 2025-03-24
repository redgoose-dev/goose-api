from typing import Optional
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def get_item(params: types.GetItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        checking_token(req, db)

        # set srl
        srl: Optional[int] = None
        code: Optional[str] = None
        try: srl = int(params.srl)
        except ValueError: code = str(params.srl)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set where
        where = []
        if srl: where.append(f'and srl={srl}')
        if code: where.append(f'and code LIKE "{code}"')

        # get item
        data = db.get_item(
            table_name = Table.APP.value,
            where = where,
            fields = fields,
        )
        if not data: raise Exception('Item not found', 204)

        # set result
        result = output.success({
            'message': 'Success get App item.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
