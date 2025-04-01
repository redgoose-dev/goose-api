from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from ..category import __libs__ as category_libs

async def delete_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.DeleteItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check item
        count = db.get_count(
            table_name = Table.NEST.value,
            where = [ f'srl = {params.srl}' ],
        )
        if count == 0: raise Exception('Item not found.', 204)

        # delete category
        category_libs.delete(db, category_libs.Module.NEST, params.srl)

        # TODO: 추가로 작업 해야할것들
        # TODO: - 아티클 데이터 삭제
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
        if not _db and db: db.disconnect()
        return result
