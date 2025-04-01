import re
from src.libs.db import DB, Table
from src.libs.resource import Patterns
from src.libs.object import compare_list

class Module:
    ARTICLE = 'article'
    JSON = 'json'
    CHECKLIST = 'checklist'

def __check_module_name__ (module: str):
    if not re.match(Patterns.tag_module, module):
        raise Exception('Invalid module name.')

def __get_tags__(tags: str|list):
    return tags.split(',') if isinstance(tags, str) else tags

def __add_tag__(tag: str, module: str, module_srl: int, _db: DB) -> int|None:
    # get tag data
    tag_data = _db.get_item(
        table_name = Table.TAG.value,
        fields = [ 'srl', 'name' ],
        where = [ f'name GLOB "{tag}"' ],
    )
    tag_id = tag_data['srl'] if (tag_data and 'srl' in tag_data) else None
    # add tag data
    if tag_id is None:
        tag_id = _db.add_item(
            table_name = Table.TAG.value,
            placeholders = [{ 'key': 'name', 'value': ':tag' }],
            values = { 'tag': tag },
        )
    if tag_id is None: return None
    # add mapping tag data
    count = _db.get_count(
        table_name = Table.MAP_TAG.value,
        where = [
            f'and tag_srl = {tag_id}',
            f'and module LIKE "{module}"',
            f'and module_srl = {module_srl}',
        ],
    )
    if count > 0: return None
    map_tag_id = _db.add_item(
        table_name = Table.MAP_TAG.value,
        placeholders = [
            { 'key': 'tag_srl', 'value': ':tag_id' },
            { 'key': 'module', 'value': ':module' },
            { 'key': 'module_srl', 'value': ':module_srl' },
        ],
        values = {
            'tag_id': tag_id,
            'module': module,
            'module_srl': module_srl,
        },
    )
    return map_tag_id

def __delete_tag__(tag: str, module: str, module_srl: int, _db: DB):
    # get tag data
    tag_data = _db.get_item(
        table_name = Table.TAG.value,
        fields = [ 'srl' ],
        where = [ f'name GLOB "{tag}"' ],
    )
    tag_id = tag_data['srl'] if (tag_data and 'srl' in tag_data) else None
    if tag_id is None: return
    # delete data from mapping table
    _db.delete_item(
        table_name = Table.MAP_TAG.value,
        where = [
            f'and tag_srl = {tag_id}',
            f'and module LIKE "{module}"',
            f'and module_srl = {module_srl}',
        ],
    )
    # get count data from mapping table
    count = _db.get_count(
        table_name = Table.MAP_TAG.value,
        where = [ f'tag_srl = {tag_id}' ],
    )
    # delete data from tag table
    if not (count > 0):
        _db.delete_item(
            table_name = Table.TAG.value,
            where = [ f'srl = {tag_id}' ],
        )

# PUBLIC MODULES

def add(_db: DB, tags: str|list, module: str, module_srl: int):
    # check module
    __check_module_name__(module)
    # get tags
    tags = __get_tags__(tags)
    # action
    for tag in tags:
        __add_tag__(tag=tag, module=module, module_srl=module_srl, _db=_db)

def update(_db: DB, new_tags: str|list, module: str, module_srl: int):
    # check module
    __check_module_name__(module)
    # set tags
    new_tags = __get_tags__(new_tags)
    # get old tags
    old_tags = _db.get_items(
        table_name = Table.TAG.value,
        fields = [ f'{Table.TAG.value}.name' ],
        join = [
            f'JOIN {Table.MAP_TAG.value} ON {Table.MAP_TAG.value}.tag_srl = {Table.TAG.value}.srl',
        ],
        where = [
            f'and module LIKE "{module}"',
            f'and module_srl = {module_srl}',
        ],
    )
    old_tags = [ item['name'] for item in old_tags ]
    # compare tags
    compare = compare_list(old_tags, new_tags)
    if not compare: return
    # action
    if 'removed' in compare and len(compare['removed']) > 0:
        for tag in compare['removed']:
            __delete_tag__(tag=tag, module=module, module_srl=module_srl, _db=_db)
    if 'added' in compare and len(compare['added']) > 0:
        for tag in compare['added']:
            __add_tag__(tag=tag, module=module, module_srl=module_srl, _db=_db)

def delete(_db: DB, tags: str|list, module: str, module_srl: int):
    # check module
    __check_module_name__(module)
    # get tags
    tags = __get_tags__(tags)
    # action
    for tag in tags:
        __delete_tag__(tag=tag, module=module, module_srl=module_srl, _db=_db)

def delete_all(_db: DB, module: str, module_srl: int):
    # check module
    __check_module_name__(module)
    # get tags
    tags_data = _db.get_items(
        table_name = Table.TAG.value,
        fields = [ f'{Table.TAG.value}.name' ],
        join = [
            f'JOIN {Table.MAP_TAG.value} ON {Table.MAP_TAG.value}.tag_srl = {Table.TAG.value}.srl',
        ],
        where = [
            f'and module LIKE "{module}"',
            f'and module_srl = {module_srl}',
        ],
    )
    tags = [item['name'] for item in tags_data]
    # action
    for tag in tags:
        __delete_tag__(tag=tag, module=module, module_srl=module_srl, _db=_db)

def get_index(module: str, module_srl: int, _db: DB) -> list:
    # check module
    __check_module_name__(module)
    # get tags
    tags = _db.get_items(
        table_name = Table.TAG.value,
        fields = [ f'{Table.TAG.value}.name' ],
        join = [
            f'JOIN {Table.MAP_TAG.value} ON {Table.MAP_TAG.value}.tag_srl = {Table.TAG.value}.srl',
        ],
        where = [
            f'and module LIKE "{module}"',
            f'and module_srl = {module_srl}',
        ],
    )
    if not (tags and len(tags) > 0): return []
    return [item['name'] for item in tags]
