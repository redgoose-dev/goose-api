from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def delete_item(params: types.DeleteItem):
    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # TODO: 인증 검사하기

        # set where
        where = [ f'and srl={params.srl}' ]

        # check item
        count = db.get_count(
            table_name = Table.NEST.value,
            where = where,
        )
        if count == 0: raise Exception('Item not found.', 204)

        # delete item
        db.delete_item(
            table_name = Table.NEST.value,
            where = where,
        )

        # TODO: 추가로 작업 해야할것들
        # TODO: - 아티클
        # TODO: - 카테고리
        # TODO: - 파일
        # TODO: - 댓글

        # set result
        result = output.success({
            'message': 'Success delete Nest.',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
