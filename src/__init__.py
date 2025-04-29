import os, toml
from src.libs.util import setup_env

# setup env
setup_env()

# load pyproject.toml
config = toml.load('pyproject.toml')

# set env
__NAME__ = os.getenv('SERVICE_NAME')
__VERSION__ = config['project']['version']
__DEV__ = os.getenv('DEV', 'False').lower() == 'true'
__DEBUG__ = os.getenv('DEBUG', 'False').lower() == 'true'
__USE_LOG__ = os.getenv('USE_LOG', 'False').lower() == 'true'
__RECORD_LOG__ = os.getenv('RECORD_LOG', 'False').lower() == 'true'
__PRINT_LOG__ = os.getenv('PRINT_LOG', 'False').lower() == 'true'
