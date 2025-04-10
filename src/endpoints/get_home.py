from fastapi import Request
from src import output, __VERSION__, __DEV__

async def get_home(req: Request = None, _db = None):

    # set values
    result = None

    try:
        result = output.success({
            'message': 'Hello goose-api',
            'version': __VERSION__,
            'dev': __DEV__,
        }, _req=req)
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        return result
