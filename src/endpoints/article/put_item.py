from . import __types__ as types
from src import output
from src.libs.db import DB, Table

# 아티클 추가
# 여기서는 mode=ready 상태의 빈 아티클만 추가한다.

async def put_item(params: types.PutItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # TODO: 인증 검사하기

        # check ready mode item
        item = db.get_item(
            table_name = Table.ARTICLE.value,
            fields = [ 'srl' ],
            where = [ f'mode LIKE "ready"' ],
        )
        if item:
            data = item['srl']
        else:
            data = db.add_item(
                table_name = Table.ARTICLE.value,
                placeholders = [{ 'key': 'mode', 'value': ':mode' }],
                values = { 'mode': 'ready' },
            )

        # set result
        result = output.success({
            'message': 'Success add Article.',
            'data': data,
        })
    except Exception as e:
        print('ERR', e)
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
