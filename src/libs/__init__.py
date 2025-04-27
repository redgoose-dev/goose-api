import os
from .util import setup_env

# setup env
setup_env()

# dir name
dir_upload = 'upload'
dir_cache = 'cache'
dir_log = 'log'

# paths
base_path = os.getenv('PATH_ROOT') or '.'
data_path = f'{base_path}/data'
url_path  = os.getenv('PATH_URL')
cache_path = f'{data_path}/{dir_cache}'
upload_path = f'{data_path}/{dir_upload}'
log_path = f'{data_path}/{dir_log}'
