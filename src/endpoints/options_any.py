from starlette.responses import Response
from .. import output

async def preflight(path_str: str) -> Response:
    return output.empty({
        'code': 204,
        'headers': {
            'Access-Control-Allow-Origin': '*',
            'Access-Control-Allow-Methods': 'GET, POST, PUT, PATCH, OPTIONS',
            'Access-Control-Allow-Headers': 'Content-Type, Authorization',
            'Access-Control-Max-Age': '86400',
        }
    }, _log=False)
