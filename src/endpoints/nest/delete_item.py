from . import __types__ as types
from src import output
from src.libs.db import DB

async def delete_item(params: types.DeleteItem):
    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # set where
        where = []
        if params.srl: where.append(f'and srl={params.srl}')

        # check item
        count = db.get_count(
            table_name = 'nest',
            where = where,
        )
        if count == 0: raise Exception('Item not found.', 204)

        # delete item
        db.delete_item(
            table_name = 'nest',
            where = where,
        )

        # TODO: 연결 되어있는 아티클,카테고리,파일,댓글 데이터 삭제하기

        # set result
        result = output.success({
            'message': 'Success delete Nest.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
