from . import __types__ as types
from src import output
from src.libs.db import DB
from src.libs.check import parse_json, check_url
from src.libs.object import json_stringify

async def patch_change_order(params: types.PatchChangeOrder):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # TODO: 인증 검사하기

        # set where
        where = []
        # if params.srl: where.append(f'and srl="{params.srl}"')

        # TODO: 작업하기

        # set result
        result = output.success({
            'message': 'Category / change-order',
        })

    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
