from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from .__libs__ import check_module

async def patch_change_order(params: types.PatchChangeOrder):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
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
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
