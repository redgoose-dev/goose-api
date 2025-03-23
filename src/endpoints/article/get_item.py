from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token

async def get_item(params: types.GetItem, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    try:
        # checking token
        db = checking_token(req, db)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set data
        data = db.get_item(
            table_name = Table.ARTICLE.value,
            fields = fields,
            where = [ f'and srl = {params.srl}' ],
        )
        if not data: raise Exception('no data', 204)
        if data and isinstance(data, dict):
            if 'json' in data: data['json'] = json_parse(data['json'])

        # TODO: mod - 카테고리 이름 가져오기
        # TODO: mod - 네스트 이름 가져오기
        # TODO: mod - 조회수 올리기

        # set result
        result = output.success({
            'message': 'Complete get Article item.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
