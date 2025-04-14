import re
from datetime import datetime
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse, json_stringify
from src.modules.verify import checking_token
from .__libs__ import Status

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
            fields=[ 'srl', 'mode', 'regdate', 'hit', 'star' ],
            where=[ f'srl = {params.srl}' ],
        )
        if not item: raise Exception('Not found article.', 204)

        # set regdate
        if not params.regdate and not item['regdate']:
            params.regdate = datetime.now().strftime('%Y-%m-%d')

        # check app
        if params.app_srl is not None:
            count = db.get_count(
                table_name=Table.APP.value,
                where=[ f'srl = {params.app_srl}' ],
            )
            if count <= 0: raise Exception('Not found app.', 400)

        # check nest
        if params.nest_srl is not None:
            count = db.get_count(
                table_name=Table.NEST.value,
                where=[ f'srl = {params.nest_srl}' ],
            )
            if count <= 0: raise Exception('Not found nest', 400)

        # check category
        if params.category_srl is not None:
            count = db.get_count(
                table_name=Table.CATEGORY.value,
                where=[ f'srl = {params.category_srl}' ],
            )
            if count <= 0: raise Exception('Not found category.', 400)

        # filtering text content
        if params.title:
            params.title = re.sub(r'\s+', ' ', params.title.strip())
            params.title = params.title.replace("'", "\\'").replace('"', '\\"')
        if params.content:
            params.content = params.content.replace("'", "\\'").replace('"', '\\"')

        # check json data
        json_data = json_parse(params.json_data) if params.json_data else None
        if params.json_data and not json_data: raise Exception('Invalid JSON data.', 400)

        # set values
        values = {}
        if params.app_srl:
            values['app_srl'] = params.app_srl
        if params.nest_srl:
            values['nest_srl'] = params.nest_srl
        if params.category_srl:
            values['category_srl'] = params.category_srl
        if params.title:
            values['title'] = params.title
        if params.content:
            values['content'] = params.content
        if params.hit:
            values['hit'] = item['hit'] + 1
        if params.star:
            values['star'] = item['star'] + 1
        if json_data:
            values['json'] = json_stringify(json_data)
        if params.mode:
            if not Status.check(params.mode): raise Exception('Invalid mode.', 400)
            values['mode'] = params.mode
        elif item['mode'] == Status.READY:
            values['mode'] = Status.PUBLIC
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
            placeholders.append('category_srl = :category_srl')
        if 'title' in values:
            placeholders.append('title = :title')
        if 'content' in values:
            placeholders.append('content = :content')
        if 'hit' in values:
            placeholders.append('hit = :hit')
        if 'star' in values:
            placeholders.append('star = :star')
        if 'json' in values:
            placeholders.append('json = :json')
        if 'mode' in values:
            placeholders.append('mode = :mode')
        if 'regdate' in values:
            placeholders.append('regdate = :regdate')
        if item['mode'] == Status.READY:
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

        # set result
        result = output.success({
            'message': 'Complete update article.',
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
