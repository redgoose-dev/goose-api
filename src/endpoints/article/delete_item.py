from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from ..file.__lib__ import delete_file
from src.modules.verify import checking_token

async def delete_item(params: types.DeleteItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        checking_token(req, db)

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

        # delete file
        files = db.get_items(
            table_name = Table.FILE.value,
            fields = [ 'srl', 'path' ],
            where = [
                'and module LIKE "article"',
                f'and module_srl = {params.srl}',
            ],
        )
        if files and len(files) > 0:
            paths = [ file['path'] for file in files ]
            for path in paths: delete_file(path)
            db.delete_item(
                table_name = Table.FILE.value,
                where = [
                    'and module LIKE "article"',
                    f'and module_srl = {params.srl}',
                ],
            )

        # delete comment
        # TODO: 연결되어있는 댓글 모두 삭제하기

        # set result
        result = output.success({
            'message': 'Success delete Article.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
