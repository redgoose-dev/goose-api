import os, mimetypes
from datetime import datetime
from src.libs.string import create_random_string

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
