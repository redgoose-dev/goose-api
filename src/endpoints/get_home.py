from fastapi import Request
from src import output
from src import __version__, __dev__

async def home(params = None, req: Request = None, _db = None):

    # set values
    result = None

    try:
        result = output.success({
            'message': 'Hello goose-api',
            'version': __version__,
            'dev': __dev__,
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        return result
