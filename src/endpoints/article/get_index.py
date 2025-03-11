import re
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.string import convert_date

async def get_index(params: types.GetIndex, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # TODO: 인증 검사하기

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set where
        where = []
        if params.app_srl:
            where.append(f'and app_srl={params.app_srl}')
        if params.nest_srl:
            where.append(f'and nest_srl={params.nest_srl}')
        if params.category_srl:
            where.append(f'and category_srl={params.category_srl}')
        if params.q:
            where.append(f'and (title LIKE "%{params.q}%" OR content LIKE "%{params.q}%")')
        if params.mode:
            where.append(f'and mode LIKE "{params.mode}"')

        # set values
        values = {}

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
            values = values,
        )
        if total == 0: raise Exception('No data', 204)

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
            limit = {
                'size': params.size,
                'page': params.page,
            },
            order = {
                'order': order,
                'sort': sort,
            },
            unlimited = params.unlimited,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success get Article index.',
            'data': {
                'total': total,
                'index': index,
            },
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
