import os
from src.libs.util import setup_env

# setup env
setup_env()

# default headers
default_headers = {
    'Authorization': os.getenv('TEST_ACCESS_TOKEN'),
}
