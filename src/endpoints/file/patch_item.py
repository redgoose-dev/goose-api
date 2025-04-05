from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse, json_stringify
from src.modules.verify import checking_token
from .__libs__ import get_unique_name, get_dir_path, write_file, delete_file
from . import __libs__ as file_libs

async def patch_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()
    file = {'content': None, 'path': '', 'name': '', 'mime': '', 'ext': ''}

    try:
        # set params
        params = types.PatchItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

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
            _json = json_parse(params.json_data)
            if 'json' in item:
                old_json = json_parse(item['json'])
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

        # change file
        _changed_file = False
        if params.file:
            # read file
            file['content'] = await params.file.read() if params.file else None
            if not file['content']: raise Exception('File not found.', 400)
            # TODO: 파일 사이즈 제한 검사
            # TODO: 파일 타입 검사
            # set file info
            file['name'] = params.file.filename
            file['mime'] = params.file.content_type
            file['ext'] = file.get('mime').split('/')[1]
            # convert file format
            if file_libs.use_convert_image_format(file.get('mime'), params.file_format):
                file = file_libs.convert_image_format(
                    file=file,
                    mime=params.file_format,
                    quality=params.file_quality,
                )
            # set path
            file['path'] = f'{get_dir_path()}/{get_unique_name(8)}.{file['ext']}'
            # copy file
            write_file(file['content'], file['path'])
            values['name'] = file['name']
            values['path'] = file['path']
            values['mime'] = file['mime']
            values['size'] = len(file['content'])
            # set image size
            if values['mime'].startswith('image/'):
                if not _json: _json = json_parse(item['json']) or {}
                _json = file_libs.update_image_size(json=_json, path=file['path'])
            _changed_file = True

        # stringify json
        if _json: values['json'] = json_stringify(_json)

        # check values
        if not bool(values): raise Exception('No values to update.', 400)

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
        if file.get('path'): delete_file(file['path'])
        result = output.exc(e)
    finally:
        if not _db and db: db.disconnect()
        return result
