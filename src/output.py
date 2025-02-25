from typing import Dict
from starlette.responses import Response
from fastapi.responses import JSONResponse
from .libs.string import create_random_string

# base headers
baseHeaders = {
    # 'Access-Control-Allow-Origin': '*',
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH',
    'Access-Control-Allow-Headers': 'Content-Type, Authorization',
    'Access-Control-Allow-Credentials': 'true',
}

# success
def success(data: Dict|None, options: Dict[str, any] = None) -> JSONResponse:
    code = options.get('code', 200) if options else 200
    headers = _get_header(options.get('headers', {}) if options else {})
    content = {
        'data': data,
    }
    return JSONResponse(content=content, status_code=code, headers=headers)

# no content
def empty(options: Dict[str, any] = None) -> Response:
    code = options.get('code', 204) if options else 204
    headers = _get_header(options.get('headers', {}) if options else {})
    return Response(status_code=code, headers=headers)

# error
def error(content: str, options: Dict[str, any] = None) -> Response:
    code = options.get('code', 500) if options else 500
    headers = options.get('headers', {}) if options else {}
    if code not in [ 404, 405 ]:
        unique = create_random_string(16)
        headers['Error-Code'] = unique
    return Response(status_code = code, content = content, headers = headers )


## private functions

# get header
def _get_header(src: Dict[str, str]) -> Dict[str, str]:
    return {**baseHeaders, **src}
