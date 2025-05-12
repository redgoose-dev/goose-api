from . import __types__ as types, __libs__ as file_libs
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse, json_stringify
from src.modules.verify import checking_token
from src.modules.preference import Preference

async def patch_item(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()
    file = {
        'content': None,
        'path': '',
        'name': '',
        'mime': '',
        'ext': '',
    }

    try:
        # set params
        params = types.PatchItem(**params)

        # checking token
        if _check_token: checking_token(req, db)

        # check item
        item = db.get_item(
            table_name=Table.FILE.value,
            where=[ f'srl = {params.srl}' ],
        )
        if not item: raise Exception('Item not found.', 204)

        # set preference
        pref = Preference()

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

        # change file
        _changed_file = False
        if params.file:
            # read file
            file['content'] = await params.file.read()
            if not file['content']: raise Exception('File not found.', 400)
            # check file size
            if pref.get('file.limitSize') < len(file['content']):
                raise Exception('File size limit exceeded.', 400)
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
            file['path'] = f'{file_libs.get_dir_path(params.dir_name)}/{file_libs.get_unique_name(8)}.{file['ext']}'
            # copy file
            file_libs.write_file(file['content'], file['path'])
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
            table_name=Table.FILE.value,
            where=[ f'srl = {params.srl}' ],
            placeholders=placeholders,
            values=values,
        )

        # delete legacy file
        if _changed_file and item['path']: file_libs.delete_file(item['path'])

        # get new item
        new_item = db.get_item(
            table_name=Table.FILE.value,
            where=[ f'srl = {params.srl}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete update file.',
            'data': {
                'srl': new_item['srl'],
                'code': new_item['code'],
                'name': new_item['name'],
                'mime': new_item['mime'],
                'size': new_item['size'],
                'json': json_parse(new_item['json']) if new_item.get('json') else None,
                'module': new_item['module'],
                'module_srl': new_item['module_srl'],
                'created_at': new_item['created_at'],
            },
        }, _req=req)
    except Exception as e:
        if file.get('path'): file_libs.delete_file(file['path'])
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
