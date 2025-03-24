from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from ..file.__lib__ import delete_file

async def delete_item(params: types.DeleteItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        checking_token(req, db)

        # set where
        where = [ f'srl = {params.srl}' ]

        # check item
        count = db.get_count(
            table_name = Table.JSON.value,
            where = where,
        )
        if count == 0: raise Exception('no data', 204)

        # delete item
        db.delete_item(
            table_name = Table.JSON.value,
            where = where,
        )

        # TODO: 파일삭제는 함수 하나로 압축할 수 있을거 같은데..
        # delete files
        files = db.get_items(
            table_name = Table.FILE.value,
            fields = ['srl', 'path'],
            where = [
                'and module LIKE "json"',
                f'and module_srl = {params.srl}',
            ],
        )
        if files and len(files) > 0:
            paths = [ file['path'] for file in files ]
            for path in paths: delete_file(path)
            db.delete_item(
                table_name = Table.FILE.value,
                where = [
                    'and module LIKE "json"',
                    f'and module_srl = {params.srl}',
                ],
            )

        # set result
        result = output.success({
            'message': 'Complete delete JSON.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
