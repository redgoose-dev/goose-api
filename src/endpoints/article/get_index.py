import re
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
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

        # set data assets
        where = []
        values = {}
        join = []

        # set base params
        if params.app_srl:
            where.append(f'AND app_srl = {params.app_srl}')
        if params.nest_srl:
            where.append(f'AND nest_srl = {params.nest_srl}')
        if params.category_srl:
            where.append(f'AND category_srl = {params.category_srl}')
        if params.q:
            where.append(f'AND (title LIKE "%{params.q}%" OR content LIKE "%{params.q}%")')
        if params.mode:
            where.append(f'AND mode LIKE "{params.mode}"')

        # set tag
        if params.tag:
            from ..tag import __libs__ as tag_lib
            _tag = ','.join(params.tag.split(','))
            where.append(f'AND article.srl IN (SELECT map_tag.module_srl FROM map_tag WHERE map_tag.module LIKE :module AND map_tag.tag_srl IN ({_tag}))')
            values['module'] = tag_lib.Module.ARTICLE

        # set duration
        if params.duration:
            duration = params.duration.split(',')
            range_maps = {
                'day': '1 day',
                'week': '7 day',
                'month': '1 month',
                'year': '1 year',
            }
            if len(duration) > 3 and re.match(r'\d{4}-\d{2}-\d{2}', duration[3]):
                sp_date = duration[3]
            else:
                sp_date = 'now'
            duration_field = duration[1]
            duration_range = range_maps.get(duration[2], '1 day')
            values['duration_date'] = sp_date
            match duration[0]:
                case 'new':
                    where.append(f'AND {duration_field} BETWEEN DATE(:duration_date, "-{duration_range}", "localtime") AND :duration_date')
                case 'old':
                    where.append(f'AND {duration_field} BETWEEN :duration_date AND DATE(:duration_date, "+{duration_range}", "localtime")')

        # get total
        total = db.get_count(
            table_name = Table.ARTICLE.value,
            where = where,
            join = join,
            values = values,
        )
        if not (total > 0): raise Exception('No data', 204)

        # set random
        if params.random:
            order = f'abs(((srl * :random * 999) + 579) % 1000)'
            sort = ''
            values['random'] = int(params.random)
        elif params.order:
            order = params.order
            sort = params.sort if params.sort else 'desc'
        else:
            order = None
            sort = None

        # get index
        index = db.get_items(
            table_name = Table.ARTICLE.value,
            fields = fields,
            where = where,
            join = join,
            limit = {
                'size': params.size,
                'page': params.page,
            },
            order = {
                'order': order,
                'sort': sort,
            },
            values = values,
            unlimited = params.unlimited,
        )
        def transform_item(item: dict) -> dict:
            if 'json' in item:
                item['json'] = json_parse(item['json'])
            return item
        index = [ transform_item(item) for item in index ]

        # TODO: mod - 카테고리 이름 가져오기
        # TODO: mod - 둥지 이름 가져오기

        # set result
        result = output.success({
            'message': 'Complete get article index.',
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
