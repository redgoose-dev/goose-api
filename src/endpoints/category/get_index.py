from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token

async def get_index(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetIndex(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set where
        where = []
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')
        if params.module:
            where.append(f'and module="{params.module}"')
        if params.module_srl is not None:
            where.append(f'and module_srl={params.module_srl}')

        # get total
        total = db.get_count(
            table_name = Table.CATEGORY.value,
            where = where,
        )
        if total == 0: raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name = Table.CATEGORY.value,
            fields = fields,
            where = where,
            limit = {
                'size': params.size,
                'page': params.page,
            },
            order = {
                'order': params.order,
                'sort': params.sort,
            },
            unlimited = params.unlimited,
        )
        # def transform_item(item: dict) -> dict:
        #     if 'created_at' in item:
        #         item['created_at'] = convert_date(item['created_at'])
        #     return item
        # index = [transform_item(item) for item in index]

        # TODO: mod - count / article or json 갯수 가져오기
        # TODO: mod - all / 모든 모듈 데이터 갯수
        # TODO: mod - none / 모듈 데이터에서 카테고리에 해당되지 않는 데이터 갯수

        # set result
        result = output.success({
            'message': 'Success get category index.',
            'data': {
                'total': total,
                'index': index,
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
