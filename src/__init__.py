import os
from src.libs.util import setup_env

# setup env
setup_env()

__name__ = 'API'
__version__ = '2.0.0'
__dev__ = os.getenv('DEV', 'False') == 'True'
