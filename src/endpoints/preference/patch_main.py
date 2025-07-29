from src import output
from src.libs.db import DB
from src.modules.verify import checking_token
from src.modules.preference import Preference
from src.libs.object import json_parse
from . import __types__ as types

async def patch_main(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchMain(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # set preference
        pref = Preference()

        # update preference
        new_json = json_parse(params.json_data)
        if json_data is None: raise Exception('Invalid JSON data.', 400)
        pref.update(new_json, params.change_data)

        # get data
        data = pref.get_all()

        # set result
        result = output.success({
            'message': 'Complete update preference.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
