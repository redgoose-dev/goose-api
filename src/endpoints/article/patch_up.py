from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

"""
hit, star 값을 증가한다.

@param {int} params.srl
@param {'hit'|'star'} params.mode
"""
async def patch_up(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchUp(**params)

        # checking token
        if _check_token: checking_token(req, db, use_public=True)

        # get item
        item = db.get_item(
            table_name=Table.ARTICLE.value,
            fields=[ 'srl', 'hit', 'star' ],
            where=[ f'srl = {params.srl}' ],
        )
        if not item: raise Exception('Not found article.', 204)

        # set values
        values = {}
        match params.mode:
            case 'hit':
                values['hit'] = item['hit'] + 1
            case 'star':
                values['star'] = item['star'] + 1

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

        # set placeholders
        placeholders = []
        if 'hit' in values:
            placeholders.append('hit = :hit')
        if 'star' in values:
            placeholders.append('star = :star')
        placeholders.append('updated_at = DATETIME("now", "localtime")')

        # update item
        db.update_item(
            table_name=Table.ARTICLE.value,
            where=[ f'srl = {params.srl}' ],
            placeholders=placeholders,
            values=values,
        )

        # get updated item
        updated_item = db.get_item(
            table_name=Table.ARTICLE.value,
            fields=[ 'hit', 'star' ],
            where=[ f'srl = {params.srl}' ],
        )

        # set count
        match params.mode:
            case 'hit':
                count = updated_item.get('hit')
            case 'star':
                count = updated_item.get('star')
            case _:
                count = None

        # set result
        result = output.success({
            'message': f'Complete up {params.mode} count.',
            'count': count,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
