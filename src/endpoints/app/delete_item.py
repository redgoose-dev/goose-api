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
        if params.srl: where.append(f'and srl="{params.srl}"')

        # check item
        count = db.get_count(
            table_name = 'app',
            where = where,
        )
        if count == 0: raise Exception('Item not found.', 204)

        # delete item
        db.delete_item(
            table_name = 'app',
            where = where,
        )

        # TODO: article, nest 데이터를 어떻게 업데이트할지 고민 필요함.
        # TODO: 자식 데이터는 삭제하는건 옳지 않다고 본다.
        # TODO: app_srl 값만 업데이트 하는것으로 처리하는게 최선이지 않을까..

        # set result
        result = output.success({
            'message': 'Success delete App.',
        })
    except Exception as e:
        match e.args[1] if len(e.args) > 1 else 500:
            case 204:
                result = output.empty({
                    'message': e.args[0],
                })
            case _:
                result = output.error(None, {
                    'error': e,
                })
    finally:
        db.disconnect()
        return result
