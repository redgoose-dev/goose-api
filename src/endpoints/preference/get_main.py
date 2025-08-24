from src import output
from src.libs.db import DB
from src.modules.verify import checking_token
from src.modules.preference import Preference

async def get_main(req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # checking token
        token = checking_token(req, db) if not _token else _token

        # set preference
        pref = Preference()

        # get data
        data = pref.get_all()

        # set result
        result = output.success({
            'message': 'Complete get preference.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
