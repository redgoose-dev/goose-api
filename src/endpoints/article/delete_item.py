from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from ..file.__libs__ import delete_files_data
from ..comment.__libs__ import delete_comment_data

async def delete_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # get item
        item = db.get_item(
            table_name = Table.ARTICLE.value,
            where = [ f'srl = {params.srl}' ],
        )
        if not item: raise Exception('Item not found', 204)

        # delete item
        db.delete_item(
            table_name = Table.ARTICLE.value,
            where = [ f'srl = {params.srl}' ],
        )

        # delete files
        delete_files_data(db, 'article', params.srl)

        # delete comment
        delete_comment_data(db, 'article', params.srl)

        # set result
        result = output.success({
            'message': 'Success delete article.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
