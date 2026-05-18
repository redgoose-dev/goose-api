import time, traceback, inspect
from typing import Dict, Any
from fastapi import Request
from pydantic import BaseModel
from starlette.responses import Response, RedirectResponse
from src.extends.Response import LocalJSONResponse
from src.modules import logger
from src.libs.string import create_random_string, color_text, get_status_message

# base headers
baseHeaders = {
    'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH',
    'Access-Control-Allow-Headers': 'Origin, Content-Type, Authorization, Accept',
    'Access-Control-Allow-Credentials': 'true',
    'Access-Control-Allow-Origin': '*',
}

### PRIVATE FUNCTIONS ###

# get header
def __get_header__(src: Dict[str, str]) -> Dict[str, str]:
    return { **baseHeaders, **src }

# get code
def __get_code__(options: dict) -> int:
    if options is None: return 500
    code = options.get('code', None)
    if isinstance(code, int): return code
    if not options.get('error'): return 500
    if isinstance(options['error'], Exception):
        return options['error'].args[1] if len(options['error'].args) > 1 else 500
    return 500

def __process_time__(req: Request, headers: dict) -> str:
    if getattr(req.state, 'start_time', None) is None: return headers
    process_time = (time.time() - req.state.start_time) * 1000
    headers['X-Process-Time'] = f'{process_time:.2f} ms'
    return headers

### PUBLIC FUNCTIONS ###

# simple text
def text(content: str = '', status_code: int = 200, _req: Request = None):
    return Response(
        content=content,
        status_code=status_code,
    )

# success
def success(
    data: Dict|None,
    options: Dict[str, any] = None,
    _req: Request = None,
    _module: str = None,
    _log: bool = True,
) -> LocalJSONResponse:
    status_code = options.get('code', 200) if options else 200
    headers = __get_header__(options.get('headers', {}) if options else {})
    content = { **data }
    # with request
    if _req:
        headers = __process_time__(_req, headers)
    # write log
    if _log:
        _module = _module if _module else inspect.stack()[1].frame.f_globals['__name__']
        logger.success(
            content.get('message', 'Unknown Message'),
            url=_req.url,
            method=_req.method,
            module=_module,
            status_code=status_code,
            user_agent=_req.headers.get('User-Agent', None),
            ip=_req.client.host,
            run_time=headers.get('X-Process-Time', None),
        )
    return LocalJSONResponse(
        status_code=status_code,
        headers=headers,
        content=content,
        indent=options.get('indent', 2) if options else 2,
    )

# buffer
def buffer(
    data: Any|None,
    options: Dict[str,any] = None,
    _req: Request = None,
    _log: bool = True,
) -> Response:
    status_code = options.get('code', 200) if options else 200
    headers = __get_header__(options.get('headers', {}) if options else {})
    # with request
    if _req: headers = __process_time__(_req, headers)
    # write log
    if _log:
        logger.success(
            'Open file',
            url=_req.url,
            method=_req.method,
            status_code=status_code,
            user_agent=_req.headers.get('User-Agent', None),
            ip=_req.client.host,
            run_time=headers.get('X-Process-Time', None),
        )
    return Response(
        status_code=status_code,
        headers=headers,
        content=data,
    )

# redirect url
def redirect(path: str, code: int = 302, _req: Request = None):
    return RedirectResponse(path, status_code=code)

# no content
def empty(
    options: Dict[str, any] = None,
    _req: Request = None,
    _module: str = None,
    _log: bool = True,
) -> Response:
    status_code = options.get('code', 204) if options else 204
    headers = __get_header__(options.get('headers', {}) if options else {})
    # with request
    if _req: headers = __process_time__(_req, headers)
    # write log
    if _log:
        _module = _module if _module else inspect.stack()[1].frame.f_globals['__name__']
        logger.success(
            'No Content',
            url=_req.url,
            method=_req.method,
            status_code=status_code,
            user_agent=_req.headers.get('User-Agent', None),
            ip=_req.client.host,
            run_time=headers.get('X-Process-Time', None),
            module=_module,
        )
    return Response(
        status_code=status_code,
        headers=headers,
    )

# error
def error(
    content: str|None,
    options: Dict[str, any] = None,
    _req: Request = None,
    _module: str = None,
    _log: bool = True,
) -> Response:
    # set code
    code = __get_code__(options)
    # set headers
    headers = __get_header__(options.get('headers', {}) if options else {})
    # set content
    if not content: content = get_status_message(code)
    # set extra
    if code not in [ 404, 405 ]:
        unique = create_random_string(12)
        headers['Error-Code'] = unique
        # with request
        if _req: headers = __process_time__(_req, headers)
        # write log
        if _log:
            _module = _module if _module else inspect.stack()[1].frame.f_globals['__name__']
            logger.error(
                content or 'Unknown Error',
                error_code=headers.get('Error-Code', None),
                url=_req.url,
                method=_req.method,
                status_code=code,
                user_agent=_req.headers.get('User-Agent', None),
                ip=_req.client.host,
                run_time=headers.get('X-Process-Time', None),
                module=_module,
                stack=options.get('stack', None),
            )
    # return
    return Response(
        status_code=code,
        headers=headers,
        content=content or 'Service Error',
    )

# exception
def exc(e: Exception, _req: Request = None, _module: str = None, _log: bool = True) -> Response:
    _module = _module if _module else inspect.stack()[1].frame.f_globals['__name__']
    match e.args[1] if len(e.args) > 1 else 500:
        case 204:
            return empty(
                options={ 'message': e.args[0] },
                _req=_req,
                _module=_module,
                _log=_log,
            )
        case _:
            return error(
                None,
                options={
                    'error': e,
                    'stack': str(traceback.format_exc()),
                },
                _req=_req,
                _module=_module,
                _log=_log,
            )
