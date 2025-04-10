from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__libs__ import check_module
from src.modules.verify import checking_token

async def patch_change_order(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchChangeOrder(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check module
        check_module(db, params.module, params.module_srl)

        # get srls
        srls = params.srls.split(',')

        # update items
        for key, srl in enumerate(srls):
            db.update_item(
                table_name = Table.CATEGORY.value,
                placeholders = [ 'turn = :turn' ],
                where = [ f'srl = {srl}' ],
                values = { 'turn': key + 1 },
            )

        # set result
        result = output.success({
            'message': 'Complete change order.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
