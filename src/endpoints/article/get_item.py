from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token
from ..tag import __libs__ as tag_libs

async def get_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

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

        # get tag
        tag_index = tag_libs.get_index(
            _db = db,
            module = tag_libs.Module.ARTICLE,
            module_srl = params.srl,
        )
        if tag_index and len(tag_index) > 0: data['tag'] = tag_index

        # TODO: mod - 카테고리 이름 가져오기
        # TODO: mod - 네스트 이름 가져오기
        # TODO: mod - 조회수 올리기

        # set result
        result = output.success({
            'message': 'Complete get article item.',
            'data': data,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
