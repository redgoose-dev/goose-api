from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.modules.verify import checking_token
from . import __libs__ as category_libs

async def put_item(params: dict, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PutItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check module
        category_libs.check_module(db, params.module, params.module_srl)

        # check module and get max turn
        where = [ f'AND module LIKE \'{params.module}\'' ]
        match params.module:
            case category_libs.Module.NEST:
                if params.module_srl:
                    where.append(f'AND module_srl = {params.module_srl}')
                else:
                    where.append(f'AND module_srl IS NULL')
            case category_libs.Module.JSON:
                where.append(f'AND module_srl IS NULL')
        count = db.get_count(
            table_name=Table.CATEGORY.value,
            where=where,
        )

        # set values
        values = {
            'name': params.name,
            'module': params.module,
            'turn': count + 1,
        }
        match params.module:
            case category_libs.Module.NEST:
                if params.module_srl: values['module_srl'] = params.module_srl

        # set placeholders
        placeholders = [
            { 'key': 'name', 'value': ':name' },
            { 'key': 'module', 'value': ':module' },
        ]
        if 'module_srl' in values:
            placeholders.append({ 'key': 'module_srl', 'value': ':module_srl' })
        placeholders.append({ 'key': 'turn', 'value': ':turn' })
        placeholders.append({ 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' })

        # add item
        data = db.add_item(
            table_name=Table.CATEGORY.value,
            placeholders=placeholders,
            values=values,
        )

        # set result
        result = output.success({
            'message': 'Complete add category.',
            'data': data,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
