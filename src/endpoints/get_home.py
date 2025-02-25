from ..output import success, empty, error
from src import __version__, __dev__

def home():
    return success({
        'message': 'Hello goose-api',
        'version': __version__,
        'dev': __dev__,
    })
