from . import __types__ as types, __libs__ as article_libs
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def patch_change_srl(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchChangeSrl(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # get article
        item = db.get_item(
            table_name=Table.ARTICLE.value,
            fields=[ 'srl', 'app_srl', 'nest_srl' ],
            where=[
                f'AND srl={params.srl}',
                f'AND mode NOT LIKE \'{article_libs.Status.READY}\''
            ],
        )
        if not item: raise Exception('Not found article.', 204)

        # check app_srl or nest_srl
        if not (params.app_srl or params.nest_srl):
            raise Exception('Not found app or nest srl.', 400)

        # set data assets
        values = {}
        placeholders = []

        # check app
        if params.app_srl and item.get('app_srl') != params.app_srl:
            count = db.get_count(
                table_name=Table.APP.value,
                where=[ f'srl = {params.app_srl}' ],
            )
            if count <= 0: raise Exception('Not found app.', 400)
            values['app_srl'] = params.app_srl
            placeholders.append('app_srl = :app_srl')

        # check nest
        if params.nest_srl and item.get('nest_srl') != params.nest_srl:
            count = db.get_count(
                table_name=Table.NEST.value,
                where=[ f'srl = {params.nest_srl}' ],
            )
            if count <= 0: raise Exception('Not found nest.', 400)
            values['nest_srl'] = params.nest_srl
            placeholders.append('nest_srl = :nest_srl')
            placeholders.append('category_srl = NULL')

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

        # update article
        db.update_item(
            table_name=Table.ARTICLE.value,
            placeholders=placeholders,
            values=values,
            where=[ f'srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete change srl from article.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
