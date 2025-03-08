from PIL import Image
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.check import parse_json
from src.libs.object import json_stringify
from .__lib__ import get_filename, get_dir_path, write_file, delete_file

async def patch_item(params: types.PatchItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    # set base
    path_file = ''

    try:
        # TODO: 인증 검사하기

        # set where
        where = [ f'and srl="{params.srl}"' ]

        # check item
        item = db.get_item(
            table_name = Table.FILE.value,
            where = where,
        )
        if not item: raise Exception('Item not found.', 204)

        # set values
        values = {}

        # set json
        _json = None
        if params.json_data:
            _json = {
                **(parse_json(item['json']) or {}),
                **parse_json(params.json_data),
            }

        # set module ot target_srl
        if params.module or params.target_srl:
            _module = params.module or item['module']
            _target_srl = params.target_srl or item['target_srl']
            match _module:
                case 'article': table_name = Table.ARTICLE.value
                case 'json': table_name = Table.JSON.value
                case 'checklist': table_name = Table.CHECKLIST.value
                case _: table_name = ''
            if not table_name: raise Exception('Module item not found.', 400)
            count = db.get_count(
                table_name = table_name,
                where = [ f'srl={_target_srl}' ],
            )
            if count <= 0: raise Exception('Module item not found.', 400)
            if params.module: values['module'] = params.module
            if params.target_srl: values['target_srl'] = params.target_srl

        # set file
        _changed_file = False
        if params.file:
            file_content = await params.file.read() if params.file else None
            if not file_content: raise Exception('File not found.', 400)
            path_file = f'{get_dir_path()}/{get_filename(8)}'
            write_file(file_content, path_file)
            values['path'] = path_file
            values['type'] = params.file.content_type
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
            raise Exception('No values to update.', 422)

        # set placeholder
        placeholders = []
        if 'module' in values and values['module']:
            placeholders.append('module = :module')
        if 'target_srl' in values and values['target_srl']:
            placeholders.append('target_srl = :target_srl')
        if 'name' in values and values['name']:
            placeholders.append('name = :name')
        if 'path' in values and values['path']:
            placeholders.append('path = :path')
        if 'type' in values and values['type']:
            placeholders.append('type = :type')
        if 'size' in values and values['size']:
            placeholders.append('size = :size')
        if 'json' in values and values['json']:
            placeholders.append('json = :json')

        # update item
        db.edit_item(
            table_name = Table.FILE.value,
            where = where,
            placeholders = placeholders,
            values = values,
        )

        # delete legacy file
        if _changed_file and item['path']: delete_file(item['path'])

        # set result
        result = output.success({
            'message': 'Success update File.',
        })
        pass
    except Exception as e:
        if path_file: delete_file(path_file)
        result = output.exc(e)
    finally:
        db.disconnect()
        return result

