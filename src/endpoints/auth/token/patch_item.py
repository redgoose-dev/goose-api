from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def patch_item(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # get token count
        count = db.get_count(
            table_name=Table.TOKEN.value,
            where=[
                f'AND srl = {params.srl}',
                f'AND expires IS NULL'
            ],
        )
        if count <= 0: raise Exception('Token not found.', 204)

        # set values
        values = {}
        if params.description:
            values['description'] = params.description

        # check values
        if not (bool(values)): raise Exception('No values to update.', 400)

        # set placeholders
        placeholders = []
        if 'description' in values:
            placeholders.append('description = :description')

        # update item
        if bool(values):
            db.update_item(
                table_name=Table.TOKEN.value,
                where=[ f'srl = {params.srl}' ],
                placeholders=placeholders,
                values=values,
            )

        # set result
        result = output.success({
            'message': 'Complete update token.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
