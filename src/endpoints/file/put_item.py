from PIL import Image
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.check import parse_json
from src.libs.object import json_stringify
from .__lib__ import get_filename, get_dir_path, write_file, delete_file

async def put_item(params: types.PutItem):

    # set values
    result = None

    # connect db
    db = DB()
    db.connect()

    # set base
    path_file = ''

    try:
        # TODO: 인증 검사하기

        # check parse json
        json_data = parse_json(params.json_data) if params.json_data else {}

        # read file
        file_content = await params.file.read() if params.file else None
        if not file_content: raise Exception('File not found.', 400)

        # print('filename:', params.file.filename)
        # print('type:', params.file.content_type)
        # print('file_content:', len(file_content))

        # TODO: 파일 사이즈 제한 검사
        # TODO: 파일 타입 검사

        # check exist target item
        where = [ f'and srl={params.target_srl}' ]
        match params.module:
            case 'article': table_name = Table.ARTICLE.value
            case 'json': table_name = Table.JSON.value
            case 'checklist': table_name = Table.CHECKLIST.value
            case _: table_name = ''
        if not table_name: raise Exception('Module item not found.', 400)
        count = db.get_count(
            table_name = table_name,
            where = where,
        )
        if count <= 0: raise Exception('Module item not found.', 400)

        # make path
        path_file = f'{get_dir_path()}/{get_filename(8)}'

        # copy file
        write_file(file_content, path_file)

        # set image size
        if params.file.content_type.startswith('image/'):
            image = Image.open(path_file)
            width, height = image.size
            json_data['width'] = width
            json_data['height'] = height

        # set values
        values = {
            'target_srl': params.target_srl,
            'name': params.file.filename,
            'path': path_file,
            'type': params.file.content_type,
            'size': len(file_content),
            'json': json_stringify(json_data),
            'module': params.module,
        }

        # set placeholders
        placeholders = [
            {'key': 'target_srl', 'value': ':target_srl'},
            {'key': 'name', 'value': ':name'},
            {'key': 'path', 'value': ':path'},
            {'key': 'type', 'value': ':type'},
            {'key': 'size', 'value': ':size'},
            {'key': 'json', 'value': ':json'},
            {'key': 'module', 'value': ':module'},
            {'key': 'created_at', 'value': 'CURRENT_TIMESTAMP'},
        ]

        # add item
        data = db.add_item(
            table_name = Table.FILE.value,
            placeholders = placeholders,
            values = values,
        )

        # set result
        result = output.success({
            'message': 'Success add File.',
            'data': data,
        })
    except Exception as e:
        if path_file: delete_file(path_file)
        result = output.exc(e)
    finally:
        db.disconnect()
        return result
