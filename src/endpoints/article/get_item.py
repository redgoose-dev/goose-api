from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token
from src.modules.mod import MOD
from . import __types__ as types, __libs__ as article_libs
from ..tag import __libs__ as tag_libs

async def get_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set base values
        params = types.GetItem(**params)
        placeholder = []

        # checking token
        if _check_token: checking_token(req, db, use_public=True)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set mod
        mod = MOD(params.mod or '')

        # set data
        data = db.get_item(
            table_name=Table.ARTICLE.value,
            fields=fields,
            where=[
                f'AND srl = {params.srl}',
                f'AND mode NOT LIKE \'{article_libs.Status.READY}\''
            ],
        )
        if not data: raise Exception('no data', 204)
        if data and isinstance(data, dict):
            if 'json' in data: data['json'] = json_parse(data['json'])

        # MOD / hit or star
        if mod.check('up-hit') or mod.check('up-star'):
            placeholder = []
            if mod.check('up-hit'): placeholder.append('hit = hit + 1')
            if mod.check('up-star'): placeholder.append('star = star + 1')

        # MOD / count-file
        if mod.check('count-file'):
            count = db.get_count(
                table_name=Table.FILE.value,
                where=[
                    f'AND module=\'article\'',
                    f'AND module_srl = {params.srl}',
                ]
            )
            data['count_file'] = count or 0

        # update data
        if len(placeholder):
            db.update_item(
                table_name = Table.ARTICLE.value,
                where = [ f'srl = {params.srl}' ],
                placeholders = placeholder,
            )

        # set result
        result = output.success({
            'message': 'Complete get article item.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
