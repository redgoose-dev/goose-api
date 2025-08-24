from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __types__ as types
from ..nest import __libs__ as nest_libs
from ..article import __libs__ as article_libs

async def delete_item(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # set where
        where = [ f'srl = {params.srl}' ]

        # check item
        count = db.get_count(
            table_name = Table.APP.value,
            where = where,
        )
        if count <= 0: raise Exception('Item not found.', 204)

        # delete articles
        articles = db.get_items(
            table_name=Table.ARTICLE.value,
            fields=[ 'srl' ],
            where=[ f'app_srl = {params.srl}' ],
        )
        if articles and len(articles) > 0:
            for article in articles: article_libs.delete(db, article.get('srl'))

        # delete nests
        nests = db.get_items(
            table_name=Table.NEST.value,
            fields=[ 'srl' ],
            where=[ f'app_srl = {params.srl}' ],
        )
        if nests and len(nests) > 0:
            for nest in nests: nest_libs.delete(db, nest.get('srl'))

        # delete item
        db.delete_item(
            table_name=Table.APP.value,
            where=where,
        )

        # set result
        result = output.success({
            'message': 'Complete delete app.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
