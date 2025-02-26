from operator import itemgetter
from typing import Dict, Any
from starlette.responses import Response
from fastapi.responses import JSONResponse
from pydantic import BaseModel
from .libs.string import create_random_string, color_text

# base headers
baseHeaders = {
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH',
    'Access-Control-Allow-Headers': 'Content-Type, Authorization',
    'Access-Control-Allow-Credentials': 'true',
}

class ResponseModel(BaseModel):
    code: int = 200,
    headers: Dict[str, str] = {}
    content: Dict[str, Any] = None

# success
def success(data: Dict|None, options: Dict[str, any] = None) -> JSONResponse:
    status_code = options.get('code', 200) if options else 200
    headers = _get_header(options.get('headers', {}) if options else {})
    content = {
        'data': data,
    }
    return JSONResponse(
        status_code = status_code,
        headers = headers,
        content = content,
    )

# no content
def empty(options: Dict[str, any] = None) -> Response:
    status_code = options.get('code', 204) if options else 204
    headers = _get_header(options.get('headers', {}) if options else {})
    return Response(
        status_code = status_code,
        headers = headers,
    )

# error
def error(content: str|None, options: Dict[str, any] = None) -> Response:
    code = options.get('code', 500) if options else 500
    headers = options.get('headers', {}) if options else {}
    if code not in [ 404, 405 ]:
        unique = create_random_string(16)
        headers['Error-Code'] = unique
        print(color_text(f'[ERROR] {options}', 'red'))
    return Response(
        status_code = code,
        headers = headers,
        content = content or 'Service Error',
    )


#### PRIVATE FUNCTIONS ####

# get header
def _get_header(src: Dict[str, str]) -> Dict[str, str]:
    return {**baseHeaders, **src}
