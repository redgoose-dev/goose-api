import re
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse
from src.modules.verify import checking_token
from src.modules.mod import MOD
from . import __types__ as types

async def get_index(params: dict = {}, req = None, _db: DB = None, _token = None):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetIndex(**params)

        # checking token
        token = checking_token(req, db, use_public=True) if not _token else _token

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set mod
        mod = MOD(params.mod or '')

        # set data assets
        where = []
        values = {}
        join = []

        # set base params
        where.append('AND mode NOT LIKE \'ready\'')
        if params.app_srl:
            where.append(f'AND app_srl = {params.app_srl}')
        if params.nest_srl:
            where.append(f'AND nest_srl = {params.nest_srl}')
        if params.category_srl is not None:
            if params.category_srl > 0:
                where.append(f'AND category_srl = {params.category_srl}')
            else:
                where.append(f'AND category_srl IS NULL')
        if params.q:
            where.append(f'AND (title LIKE "%{params.q}%" OR content LIKE "%{params.q}%")')
        if token['public']:
            where.append(f'AND mode LIKE \'public\'')
        elif params.mode:
            where.append(f'AND mode LIKE \'{params.mode}\'')

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
                case 'old':
                    where.append(f'AND ({duration_field} BETWEEN DATETIME(:duration_date, "-{duration_range}", "localtime") AND DATETIME(:duration_date, "-1 day", "localtime"))')
                case 'new':
                    where.append(f'AND ({duration_field} BETWEEN DATETIME(:duration_date, "+1 day", "localtime") AND DATETIME(:duration_date, "+{duration_range}", "localtime"))')

        # get total
        total = db.get_count(
            table_name=Table.ARTICLE.value,
            column_name=f'DISTINCT {Table.ARTICLE.value}.srl',
            where=where,
            join=join,
            values=values,
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
            table_name=Table.ARTICLE.value,
            fields=fields,
            where=where,
            join=join,
            limit={ 'size': params.size, 'page': params.page },
            order={ 'order': order, 'sort': sort },
            values=values,
            unlimited=params.unlimited,
            duplicate=False,
        )

        # transform items
        def transform_item(item: dict) -> dict:
            if 'json' in item:
                item['json'] = json_parse(item['json'])
            # MOD / app
            if mod.check('app'):
                if item.get('app_srl'):
                    from ..app import __libs__ as app_libs
                    item['app'] = app_libs.get_item(
                        _db=db,
                        srl=item.get('app_srl'),
                        fields=[ 'srl', 'code', 'name' ],
                    )
                else:
                    item['app'] = None
            # MOD / nest
            if mod.check('nest'):
                if item.get('nest_srl'):
                    from ..nest import __libs__ as nest_libs
                    item['nest'] = nest_libs.get_item(
                        _db=db,
                        srl=item.get('nest_srl'),
                        fields=[ 'srl', 'code', 'name' ],
                    )
                else:
                    item['nest'] = None
            # MOD / category
            if mod.check('category'):
                if item.get('category_srl'):
                    from ..category import __libs__ as category_libs
                    item['category'] = category_libs.get_item(
                        _db=db,
                        srl=item.get('category_srl'),
                        fields=[ 'srl', 'name' ],
                    )
                else:
                    item['category'] = None
            # MOD / tag
            if mod.check('tag'):
                from ..tag import __libs__ as tag_libs
                item['tag'] = tag_libs.get_index(
                    _db=db,
                    module=tag_libs.Module.ARTICLE,
                    module_srl=item.get('srl'),
                )
            # MOD / file
            if mod.check('file'):
                from ..file import __libs__ as file_libs
                item['file'] = file_libs.get_index(
                    _db=db,
                    module=file_libs.Module.ARTICLE,
                    module_srl=item.get('srl'),
                )
            return item
        index = [ transform_item(item) for item in index ]

        # set result
        result = output.success({
            'message': 'Complete get article index.',
            'data': {
                'total': total,
                'index': index,
            },
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
