from PIL import Image
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.check import parse_json
from src.libs.object import json_stringify
from .__lib__ import get_unique_name, get_dir_path, write_file, delete_file

async def patch_item(params: types.PatchItem, _db: DB = None):

    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    # set base
    path_file = ''

    try:
        # check item
        item = db.get_item(
            table_name = Table.FILE.value,
            where = [ f'srl = {params.srl}' ],
        )
        if not item: raise Exception('Item not found.', 204)

        # set values
        values = {}

        # set json
        _json = None
        if params.json_data:
            _json = parse_json(params.json_data)
            if 'json' in item:
                old_json = parse_json(item['json'])
                if 'width' in old_json: _json['width'] = old_json['width']
                if 'height' in old_json: _json['height'] = old_json['height']

        # set module ot module_srl
        if params.module or params.module_srl:
            _module = params.module or item['module']
            _module_srl = params.module_srl or item['module_srl']
            match _module:
                case 'article': table_name = Table.ARTICLE.value
                case 'json': table_name = Table.JSON.value
                case 'checklist': table_name = Table.CHECKLIST.value
                case 'comment': table_name = Table.COMMENT.value
                case _: table_name = ''
            if not table_name: raise Exception('Module item not found.', 400)
            count = db.get_count(
                table_name = table_name,
                where = [ f'srl = {_module_srl}' ],
            )
            if not (count > 0): raise Exception('Module item not found.', 400)
            if params.module: values['module'] = params.module
            if params.module_srl: values['module_srl'] = params.module_srl

        # set file
        _changed_file = False
        if params.file:
            file_content = await params.file.read() if params.file else None
            if not file_content: raise Exception('File not found.', 400)
            path_file = f'{get_dir_path()}/{get_unique_name(8)}'
            write_file(file_content, path_file)
            values['name'] = params.file.filename
            values['path'] = path_file
            values['mime'] = params.file.content_type
            values['size'] = len(file_content)
            if params.file.content_type.startswith('image/'):
                image = Image.open(path_file)
                width, height = image.size
                if not _json: _json = parse_json(item['json']) or {}
                _json['width'] = width
                _json['height'] = height
            _changed_file = True

        # stringify json
        if _json: values['json'] = json_stringify(_json)

        # check values
        if not bool(values):
            raise Exception('No values to update.', 400)

        # set placeholder
        placeholders = []
        if 'module' in values and values['module']:
            placeholders.append('module = :module')
        if 'module_srl' in values and values['module_srl']:
            placeholders.append('module_srl = :module_srl')
        if 'name' in values and values['name']:
            placeholders.append('name = :name')
        if 'path' in values and values['path']:
            placeholders.append('path = :path')
        if 'mime' in values and values['mime']:
            placeholders.append('mime = :mime')
        if 'size' in values and values['size']:
            placeholders.append('size = :size')
        if _json:
            placeholders.append('json = :json')

        # update item
        db.update_item(
            table_name = Table.FILE.value,
            where = [ f'srl = {params.srl}' ],
            placeholders = placeholders,
            values = values,
        )

        # delete legacy file
        if _changed_file and item['path']: delete_file(item['path'])

        # set result
        result = output.success({
            'message': 'Complete update File.',
        })
    except Exception as e:
        if path_file: delete_file(path_file)
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
