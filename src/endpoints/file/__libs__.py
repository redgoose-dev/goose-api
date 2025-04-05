import os, io, mimetypes, pillow_avif
from PIL import Image
from datetime import datetime
from src.libs.db import DB, Table
from src.libs.string import create_random_string
from src.libs.object import json_parse

class Module:
    ARTICLE = 'article'
    JSON = 'json'
    CHECKLIST = 'checklist'
    COMMENT = 'comment'

def get_unique_name(n: len = 12) -> str:
    current_time = datetime.now().strftime('%Y%m%d%H%M%S')
    unique = create_random_string(n)
    return f'{current_time}-{unique}'

def get_dir_path() -> str:
    base_path = f'{os.getenv('PATH_ROOT')}data/upload/origin'
    year = datetime.now().strftime('%Y')
    month = datetime.now().strftime('%m')
    path = f'{base_path}/{year}/{month}'
    if not os.path.isdir(path):
        os.makedirs(path)
    return path

def get_file_name(path: str) -> str:
    if not path: return ''
    return os.path.basename(path)

def get_mime_type(path: str) -> str:
    mime_type, _ = mimetypes.guess_type(path)
    if not mime_type: raise Exception('MIME type not found.')
    return mime_type

def write_file(content: bytes, path: str):
    with open(path, 'wb') as f: f.write(content)

def delete_file(path: str):
    if os.path.isfile(path): os.remove(path)

def convert_path_to_buffer(path: str) -> bytes|None:
    if not os.path.isfile(path):
        return None
    with open(path, 'rb') as file:
        return file.read()

def use_convert_image_format(mime1: str, mime2: str) -> bool:
    if not mime1 or not mime2: return False
    mime1 = mime1.split('/')
    mime2 = mime2.split('/')
    if mime1[0] != 'image' or mime2[0] != 'image': return False
    if mime1[1] == mime2[1]: return False
    return True

def convert_image_format(file: dict, mime: str, quality: int = 95):
    file['mime'] = mime
    file['ext'] = file['mime'].split('/')[1]
    image = Image.open(io.BytesIO(file['content']))
    metadata = image.info
    output_io = io.BytesIO()
    image.save(
        fp=output_io,
        format=file['ext'],
        quality=quality,
        **metadata,
    )
    file['content'] = output_io.getvalue()
    file['name'] = change_file_extension(file['name'], file['ext'])
    return file

def change_file_extension(path: str, ext: str) -> str:
    if not path: return ''
    base = os.path.splitext(path)[0]
    return f'{base}.{ext}'

def update_image_size(json: dict, path: str) -> dict:
    image = Image.open(path)
    width, height = image.size
    json['width'] = width
    json['height'] = height
    return json

# PUBLIC MODULES

def delete(db: DB, module: str, srl: int):
    files = db.get_items(
        table_name = Table.FILE.value,
        fields = ['srl', 'path'],
        where = [
            f'and module LIKE "{module}"',
            f'and module_srl = {srl}',
        ],
    )
    if files and len(files) > 0:
        paths = [file['path'] for file in files]
        for path in paths: delete_file(path)
        db.delete_item(
            table_name = Table.FILE.value,
            where = [
                f'and module LIKE "{module}"',
                f'and module_srl = {srl}',
            ],
        )
