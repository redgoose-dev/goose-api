from . import __types__ as types, __libs__ as file_libs
from fastapi import UploadFile
from src import output
from src.libs.db import DB, Table
from src.libs.object import json_parse, json_stringify
from src.libs.string import create_random_string
from src.modules.verify import checking_token
from src.modules.preference import Preference

async def put_item(params: dict = {}, req = None, _db: DB = None, _token = None):

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
        params = types.PutItem(**params)

        # checking token
        token = checking_token(req, db) if not _token else _token

        # check parse json
        json_data = json_parse(params.json_data) if params.json_data else {}

        # read file
        _file: UploadFile = params.file
        file['content'] = await _file.read() if _file else None
        if not file['content']: raise Exception('File not found.', 400)

        # set preference
        pref = Preference()

        # check file size
        if pref.get('file.limitSize') < len(file['content']):
            raise Exception('File size limit exceeded.', 400)

        # check exist module item
        match params.module:
            case 'article': table_name = Table.ARTICLE.value
            case 'json': table_name = Table.JSON.value
            case 'checklist': table_name = Table.CHECKLIST.value
            case 'comment': table_name = Table.COMMENT.value
            case _: table_name = ''
        if not table_name: raise Exception('Module item not found.', 400)
        count = db.get_count(
            table_name=table_name,
            where=[ f'srl = {params.module_srl}' ],
        )
        if not (count > 0): raise Exception('Module item not found.', 400)

        # set file info
        file['name'] = _file.filename
        file['mime'] = _file.content_type
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

        # set image size
        if file['mime'].startswith('image/'):
            json_data = file_libs.update_image_size(json=json_data, path=file['path'])

        # set values
        values = {
            'name': file['name'],
            'code': create_random_string(16),
            'path': file['path'],
            'mime': file['mime'],
            'size': len(file['content']),
            'json': json_stringify(json_data),
            'module': params.module,
            'module_srl': params.module_srl,
        }

        # set placeholders
        placeholders = [
            { 'key': 'name', 'value': ':name' },
            { 'key': 'code', 'value': ':code' },
            { 'key': 'path', 'value': ':path' },
            { 'key': 'mime', 'value': ':mime' },
            { 'key': 'size', 'value': ':size' },
            { 'key': 'json', 'value': ':json' },
            { 'key': 'module', 'value': ':module' },
            { 'key': 'module_srl', 'value': ':module_srl' },
            { 'key': 'created_at', 'value': 'DATETIME("now", "localtime")' },
        ]

        # add item
        data = db.add_item(
            table_name = Table.FILE.value,
            placeholders = placeholders,
            values = values,
        )

        # get item
        new_item = db.get_item(
            table_name=Table.FILE.value,
            where=[ f'srl = {data}' ],
        )

        # set result
        result = output.success({
            'message': 'Complete add file.',
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
        if file['path']: file_libs.delete_file(file['path'])
        result = output.exc(e, _req=req)
    finally:
        if not _db and db: db.disconnect()
        return result
