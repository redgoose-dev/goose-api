from typing import Dict, Any
from starlette.responses import Response
from src.extends.Response import LocalJSONResponse
from pydantic import BaseModel
from .libs.string import create_random_string, color_text, get_status_message

# base headers
baseHeaders = {
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH',
    'Access-Control-Allow-Headers': 'Content-Type, Authorization',
    'Access-Control-Allow-Credentials': 'true',
}

### PRIVATE FUNCTIONS ###

# get header
def _get_header(src: Dict[str, str]) -> Dict[str, str]:
    return {**baseHeaders, **src}

### PUBLIC FUNCTIONS ###

class ResponseModel(BaseModel):
    code: int = 200,
    headers: Dict[str, str] = {}
    content: Dict[str, Any] = None

# success
def success(data: Dict|None, options: Dict[str, any] = None) -> LocalJSONResponse:
    status_code = options.get('code', 200) if options else 200
    headers = _get_header(options.get('headers', {}) if options else {})
    content = {
        **data,
    }
    return LocalJSONResponse(
        status_code = status_code,
        headers = headers,
        content = content,
        indent = options.get('indent', 2) if options else 2,
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
    # set code
    code = options.get('code', None) if options else None
    if code is not None and options.get('error'):
        if isinstance(options['error'], list) and len(options['error']) > 1:
            code = options['error'].args[1]
        elif isinstance(options['error'], dict):
            code = options['error'].args[1] if len(options['error'].args) > 1 else 500
    if not code or not isinstance(code, int): code = 500
    # set headers
    headers = options.get('headers', {}) if options else {}
    # set content
    if not content: content = get_status_message(code)
    # set extra
    if code not in [ 404, 405 ]:
        unique = create_random_string(16)
        headers['Error-Code'] = unique
        print(color_text(f'[ERROR] {options}', 'red'))
    # return
    return Response(
        status_code = code,
        headers = headers,
        content = content or 'Service Error',
    )
