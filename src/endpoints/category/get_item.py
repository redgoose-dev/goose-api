from . import __types__ as types
from src import output
from src.libs.db import DB
from src.libs.check import parse_json, check_url
from src.libs.object import json_stringify

async def get_item(params: types.GetItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        print(params)
        result = {
            "message": "Category / get-item",
        }

    except Exception as e:
        result = output.error(None, {
            'error': e,
        })
    finally:
        db.disconnect()
        return result
