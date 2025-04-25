import os, io, json, mimetypes, pillow_avif
from PIL import Image
from datetime import datetime
from pathlib import Path
from src import libs
from src.libs.db import DB, Table
from src.libs.string import create_random_string

class Module:
    ARTICLE = 'article'
    JSON = 'json'
    CHECKLIST = 'checklist'
    COMMENT = 'comment'

class Status:
    PUBLIC = 'public'
    PRIVATE = 'private'
    READY = 'ready'
    @staticmethod
    def filter(value: str) -> str:
        match value:
            case Status.PUBLIC: return Status.PUBLIC
            case Status.PRIVATE: return Status.PRIVATE
            case Status.READY: return Status.READY
            case _: return Status.PUBLIC

def get_unique_name(n: len = 12) -> str:
    current_time = datetime.now().strftime('%Y%m%d%H%M%S')
    unique = create_random_string(n)
    return f'{current_time}-{unique}'

def get_dir_path(dir_name: str = 'origin') -> str:
    base_path = f'{libs.upload_path}/{dir_name}'
    year = datetime.now().strftime('%Y')
    month = datetime.now().strftime('%m')
    path = f'{base_path}/{year}/{month}'
    if not os.path.isdir(path): os.makedirs(path)
    return path

def get_file_name(path: str) -> str:
    if not path: return ''
    return os.path.basename(path)

def get_mime_type(path: str) -> str:
    mime_type, _ = mimetypes.guess_type(path)
    if not mime_type: raise Exception('MIME type not found.')
    return mime_type

def exist_file(path: str, use_throw: bool = False) -> bool:
    is_file = os.path.isfile(path)
    if use_throw and not is_file:
        raise FileNotFoundError(f"File '{path}' does not exist.")
    else:
        return is_file

def open_file(path: str, mode:str = 'json'):
    with open(path, 'r') as file:
        match mode:
            case 'json':
                data = json.load(file)
            case _:
                data = file
    return data

def write_file(content: bytes, path: str):
    with open(path, 'wb') as f: f.write(content)

def delete_file(path: str):
    if os.path.isfile(path): os.remove(path)

# 캐시 파일 삭제
def delete_cache_files(code: str):
    pattern = f'**/{code}*'
    for path in Path(libs.cache_path).glob(pattern):
        if path.is_file():
            try: path.unlink()
            except Exception as _: pass

def convert_path_to_buffer(path: str) -> bytes|None:
    if not os.path.isfile(path): return None
    with open(path, 'rb') as file: return file.read()

def get_mime_from_buffer(buffer: bytes) -> str:
    if not buffer: return ''
    mime = mimetypes.guess_type('file')[0]
    if not mime: raise Exception('MIME type not found.')
    return mime

def use_convert_image_format(mime1: str, mime2: str) -> bool:
    if not mime1 or not mime2: return False
    mime1 = mime1.split('/')
    mime2 = mime2.split('/')
    if mime1[0] != 'image' or mime2[0] != 'image': return False
    if mime1[1] == mime2[1]: return False
    return True

# 이미지 포맷 변환
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

# 파일 확장자 변경
def change_file_extension(path: str, ext: str) -> str:
    if not path: return ''
    base = os.path.splitext(path)[0]
    return f'{base}.{ext}'

# 이미지 사이즈 업데이트
def update_image_size(json: dict, path: str) -> dict:
    image = Image.open(path)
    width, height = image.size
    json['width'] = width
    json['height'] = height
    return json

# 모듈 데이터 가져오기
def get_module(db: DB, module: str, srl: int):
    if not module or not srl: return None
    match module:
        case Module.ARTICLE:
            table = Table.ARTICLE.value
        case Module.JSON:
            table = Table.JSON.value
        case Module.CHECKLIST:
            table = Table.CHECKLIST.value
        case Module.COMMENT:
            table = Table.COMMENT.value
        case _:
            table = None
    if not table: return None
    data = db.get_item(
        table_name=table,
        where=[ f'srl={srl}' ],
    )
    return data


# PUBLIC MODULES

def get_index(_db: DB, module: str, module_srl: int) -> list:
    return _db.get_items(
        table_name = Table.FILE.value,
        fields=[ 'srl', 'code', 'mime' ],
        where=[
            f'AND module LIKE \'{module}\'',
            f'AND module_srl = {module_srl}',
        ],
    )

def delete(db: DB, module: str, srl: int):
    files = db.get_items(
        table_name = Table.FILE.value,
        fields=['srl', 'code', 'path'],
        where=[
            f'AND module LIKE \'{module}\'',
            f'AND module_srl = {srl}',
        ],
    )
    if files and len(files) > 0:
        # delete files
        for file in files:
            if file.get('path'): delete_file(file.get('path'))
            if file.get('code'): delete_cache_files(file.get('code'))
        # delete data
        db.delete_item(
            table_name = Table.FILE.value,
            where = [
                f'AND module LIKE \'{module}\'',
                f'AND module_srl = {srl}',
            ],
        )
