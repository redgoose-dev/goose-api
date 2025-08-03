from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from src.modules.mod import MOD
from . import __libs__ as category_libs
from ..article import __libs__ as article_libs
from ..json import __libs__ as json_libs

async def get_index(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetIndex(**params)

        # checking token
        if _check_token: checking_token(req, db, use_public=True)

        # set fields
        fields = params.fields.split(',') if params.fields else None

        # set mod
        mod = MOD(params.mod or '')

        # set tag
        tags = ','.join(params.tag.split(',')) if params.tag and params.module else None

        # set where
        where = []
        if params.name:
            where.append(f'and name LIKE "%{params.name}%"')
        if params.module:
            where.append(f'and module LIKE \'{params.module}\'')
        if params.module_srl is not None:
            if params.module_srl > 0:
                where.append(f'and module_srl = {params.module_srl}')
            else:
                where.append(f'and module_srl IS NULL')

        # get total
        total = db.get_count(
            table_name=Table.CATEGORY.value,
            where=where,
        )
        if total == 0: raise Exception('No data', 204)

        # get index
        index = db.get_items(
            table_name=Table.CATEGORY.value,
            fields=fields,
            where=where,
            limit={ 'size': params.size, 'page': params.page },
            order={ 'order': params.order, 'sort': params.sort },
            unlimited=params.unlimited,
        )

        # set where for module
        where = []
        if params.q:
            match params.module:
                case category_libs.Module.NEST:
                    where.append(f'and (title LIKE "%{params.q}%" OR content LIKE "%{params.q}%")')
                case category_libs.Module.JSON:
                    where.append(f'and (name LIKE "%{params.q}%" OR description LIKE "%{params.q}%")')

        # transform items
        def transform_item(item: dict) -> dict:
            if 'srl' in item and 'module' in item:
                item_where = [ f'and category_srl = {item['srl']}' ]
                item_where.extend(where)
                # MOD / count
                if mod.check('count'):
                    if tags:
                        item_where.append(get_tag_count_query(params.module, tags))
                    match params.module:
                        case category_libs.Module.NEST:
                            item['count'] = article_libs.get_count(db, item_where)
                        case category_libs.Module.JSON:
                            item['count'] = json_libs.get_count(db, item_where)
            return item
        index = [ transform_item(item) for item in index ]

        # MOD / none
        if mod.check('none'):
            new_item = { 'name': 'none' }
            _where = [ f'and category_srl IS NULL' ]
            _where.extend(where)
            if mod.check('count'):
                if tags:
                    _where.append(get_tag_count_query(params.module, tags))
                match params.module:
                    case category_libs.Module.NEST:
                        if params.module_srl:
                            _where.append(f'and nest_srl = {params.module_srl}')
                        new_item['count'] = article_libs.get_count(db, _where)
                    case category_libs.Module.JSON:
                        new_item['count'] = json_libs.get_count(db, _where)
            index.append(new_item)

        # MOD / all
        if mod.check('all'):
            new_item = { 'name': 'all' }
            _where = []
            _where.extend(where)
            if mod.check('count'):
                if tags:
                    _where.append(get_tag_count_query(params.module, tags))
                match params.module:
                    case category_libs.Module.NEST:
                        if params.module_srl:
                            _where.append(f'and nest_srl = {params.module_srl}')
                        new_item['count'] = article_libs.get_count(db, _where)
                    case category_libs.Module.JSON:
                        new_item['count'] = json_libs.get_count(db, _where)
            index.insert(0, new_item)

        # set result
        result = output.success({
            'message': 'Complete get category index.',
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

def get_tag_count_query(module: str, tag: str) -> str:
    if not module or not tag: return ''
    return f'AND json.srl IN (SELECT map_tag.module_srl FROM map_tag WHERE map_tag.module LIKE \'{module}\' AND map_tag.tag_srl IN ({tag}))'
