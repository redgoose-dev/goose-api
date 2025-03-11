import re
from datetime import datetime
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.string import get_date, date_format
from src.libs.object import json_parse, json_stringify

async def patch_item(params: types.PatchItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    try:
        # TODO: 인증 검사하기

        # get item
        item = db.get_item(
            table_name = Table.ARTICLE.value,
            fields = [ 'srl', 'mode', 'regdate', 'hit', 'star' ],
            where = [ f'srl={params.srl}' ],
        )
        if not item: raise Exception('Not found Article', 204)

        # set regdate
        if not params.regdate and not item['regdate']:
            params.regdate = datetime.now().strftime('%Y-%m-%d')

        # check app
        if params.app_srl is not None:
            count = db.get_count(
                table_name = Table.APP.value,
                where = [ f'srl={params.app_srl}' ],
            )
            if count <= 0: raise Exception('Not found App', 400)

        # check nest
        if params.nest_srl is not None:
            count = db.get_count(
                table_name = Table.NEST.value,
                where = [ f'srl={params.nest_srl}' ],
            )
            if count <= 0: raise Exception('Not found Nest', 400)

        # check category
        if params.category_srl is not None:
            count = db.get_count(
                table_name = Table.CATEGORY.value,
                where = [ f'srl={params.category_srl}' ],
            )
            if count <= 0: raise Exception('Not found Category', 400)

        # filtering text content
        if params.title:
            params.title = re.sub(r'\s+', ' ', params.title.strip())
            params.title = params.title.replace("'", "\\'").replace('"', '\\"')
        if params.content:
            params.content = params.content.replace("'", "\\'").replace('"', '\\"')

        # check json data
        json_data = json_parse(params.json_data) if params.json_data else None

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
            values['mode'] = params.mode
        elif item['mode'] == 'ready':
            values['mode'] = 'public'
        if params.regdate:
            values['regdate'] = params.regdate

        # set placeholders
        placeholders = []
        if 'app_srl' in values and values['app_srl']:
            placeholders.append('app_srl = :app_srl')
        if 'nest_srl' in values and values['nest_srl']:
            placeholders.append('nest_srl = :nest_srl')
        if 'category_srl' in values and values['category_srl']:
            placeholders.append('category_srl = :category_srl')
        if 'title' in values and values['title']:
            placeholders.append('title = :title')
        if 'content' in values and values['content']:
            placeholders.append('content = :content')
        if 'hit' in values and values['hit']:
            placeholders.append('hit = :hit')
        if 'star' in values and values['star']:
            placeholders.append('star = :star')
        if 'json' in values and values['json']:
            placeholders.append('json = :json')
        if 'mode' in values and values['mode']:
            placeholders.append('mode = :mode')
        if 'regdate' in values and values['regdate']:
            placeholders.append('regdate = :regdate')
        if item['mode'] == 'ready':
            placeholders.append(f'created_at = DATETIME("now", "localtime")')
        placeholders.append(f'updated_at = DATETIME("now", "localtime")')

        # update item
        db.update_item(
            table_name = Table.ARTICLE.value,
            where = [ f'srl={params.srl}' ],
            placeholders = placeholders,
            values = values,
        )

        result = output.success({
            'message': 'Success update Article.',
        })
        pass
    except Exception as e:
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
