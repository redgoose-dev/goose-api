from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def delete_item(params: types.DeleteItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        db = checking_token(req, db)

        # check item
        count = db.get_count(
            table_name = Table.NEST.value,
            where = [ f'srl = {params.srl}' ],
        )
        if count == 0: raise Exception('Item not found.', 204)

        # TODO: 추가로 작업 해야할것들
        # TODO: - 카테고리 삭제
        # TODO: - 아티클 목록 가져오기
        # TODO: - 아티클에 해당되는 파일 삭제
        # TODO: - 아티클에 해당되는 댓글 삭제

        # delete item
        db.delete_item(
            table_name = Table.NEST.value,
            where = [ f'srl={params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete delete nest.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
