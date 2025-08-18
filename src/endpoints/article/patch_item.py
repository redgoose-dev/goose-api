import re
from datetime import datetime
from . import __types__ as types, __libs__ as article_libs
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse, json_stringify
from src.modules.verify import checking_token

async def patch_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.PatchItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # get item
        item = db.get_item(
            table_name=Table.ARTICLE.value,
            fields=[ 'srl', 'mode', 'regdate' ],
            where=[ f'srl = {params.srl}' ],
        )
        if not item: raise Exception('Not found article.', 204)

        # set regdate
        if not params.regdate and not item['regdate']:
            params.regdate = datetime.now().strftime('%Y-%m-%d')

        # check app
        if params.app_srl:
            count = db.get_count(
                table_name=Table.APP.value,
                where=[ f'srl = {params.app_srl}' ],
            )
            if count <= 0: raise Exception('Not found app.', 400)

        # check category
        if params.category_srl is not None and params.category_srl > 0:
            count = db.get_count(
                table_name=Table.CATEGORY.value,
                where=[ f'srl = {params.category_srl}' ],
            )
            if count <= 0: raise Exception('Not found category.', 400)

        # check nest
        if params.nest_srl:
            count = db.get_count(
                table_name=Table.NEST.value,
                where=[ f'srl = {params.nest_srl}' ],
            )
            if count <= 0: raise Exception('Not found nest', 400)

        # filtering text content
        if params.title:
            params.title = re.sub(r'\s+', ' ', params.title.strip())

        # check json data
        json_data = json_parse(params.json_data) if params.json_data else None
        if params.json_data and json_data is None: raise Exception('Invalid JSON data.', 400)

        # set values
        values = {}
        if params.app_srl:
            values['app_srl'] = params.app_srl
        if params.nest_srl:
            values['nest_srl'] = params.nest_srl
        if params.category_srl is not None:
            values['category_srl'] = params.category_srl or 0
        if params.title:
            values['title'] = params.title
        if params.content:
            values['content'] = params.content
        if json_data:
            values['json'] = json_stringify(json_data)
        if params.mode:
            if not article_libs.Status.check(params.mode): raise Exception('Invalid mode.', 400)
            values['mode'] = params.mode
        elif item['mode'] == article_libs.Status.READY:
            values['mode'] = article_libs.Status.PUBLIC
        if params.regdate:
            values['regdate'] = params.regdate

        # check values
        if not (bool(values) or params.tag): raise Exception('No values to update.', 400)

        # set placeholders
        placeholders = []
        if 'app_srl' in values:
            placeholders.append('app_srl = :app_srl')
        if 'nest_srl' in values:
            placeholders.append('nest_srl = :nest_srl')
        if 'category_srl' in values:
            placeholders.append(f'category_srl = {':category_srl' if values['category_srl'] > 0 else 'NULL'}')
        if 'title' in values:
            placeholders.append('title = :title')
        if 'content' in values:
            placeholders.append('content = :content')
        if 'json' in values:
            placeholders.append('json = :json')
        if 'mode' in values:
            placeholders.append('mode = :mode')
        if 'regdate' in values:
            placeholders.append('regdate = :regdate')
        if item['mode'] == article_libs.Status.READY:
            placeholders.append(f'created_at = DATETIME("now", "localtime")')
        placeholders.append('updated_at = DATETIME("now", "localtime")')

        # update item
        if bool(values):
            db.update_item(
                table_name=Table.ARTICLE.value,
                where=[ f'srl = {params.srl}' ],
                placeholders=placeholders,
                values=values,
            )

        # update tag
        if params.tag:
            from ..tag import __libs__ as tag_libs
            tag_libs.update(
                _db=db,
                new_tags=params.tag,
                module=tag_libs.Module.ARTICLE,
                module_srl=params.srl,
            )

        # clear file cache
        if item['mode'] != values.get('mode'):
            from ..file import __libs__ as file_libs
            files = file_libs.get_index(
                _db=db,
                module='article',
                module_srl=params.srl,
            )
            if files and len(files) > 0:
                for file in files:
                    if file.get('code'): file_libs.delete_cache_files(file.get('code'))

        # set result
        result = output.success({
            'message': 'Complete update article.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
