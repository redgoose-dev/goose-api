import os, time
from datetime import datetime
from fastapi import FastAPI, Request
from fastapi.responses import Response, JSONResponse
from fastapi.exceptions import RequestValidationError
from starlette.exceptions import HTTPException as StarletteHTTPException
from .libs.string import color_text
from .output import error, exc

# docs
# - Request Class: https://fastapi.tiangolo.com/ko/reference/request/?h=request#fastapi.Request

# set router
api = FastAPI()

# debug
__DEBUG__ = os.getenv('DEBUG', '').lower() == 'true'

# middleware - checking process time
@api.middleware('http')
async def _http(req: Request, call_next):
    now = None
    # start time record
    start_time = time.time()
    # print start debug
    if __DEBUG__:
        now = datetime.now().strftime('%H:%M:%S')
        print(color_text(f'\n***** | START | {now} | [{req.method}] {req.url} | *****', 'cyan'))
    # action endpoint
    response = await call_next(req)
    # end time record
    process_time = (time.time() - start_time) * 1000
    response.headers['X-Process-Time'] = f'{process_time:.2f} ms'
    # print end debug
    if __DEBUG__:
        print(color_text(f'***** | END | {now} | *****', 'cyan'))
    return response

# preflight
@api.options('/{path_str:path}')
async def _options(path_str: str) -> Response:
    from .endpoints.options_any import preflight
    return await preflight(path_str)

# home
@api.get('/')
async def _home() -> JSONResponse:
    from .endpoints.get_home import home
    return await home()

# app
from .endpoints.app import router as app
api.include_router(app, prefix='/app')

# article
from .endpoints.article import router as article
api.include_router(article, prefix='/article')

# category
from .endpoints.category import router as category
api.include_router(category, prefix='/category')

# checklist
from .endpoints.checklist import router as checklist
api.include_router(checklist, prefix='/checklist')

# comment
from .endpoints.comment import router as comment
api.include_router(comment, prefix='/comment')

# file
from .endpoints.file import router as file
api.include_router(file, prefix='/file')

# json
from .endpoints.json import router as json
api.include_router(json, prefix='/json')

# nest
from .endpoints.nest import router as nest
api.include_router(nest, prefix='/nest')

# auth
from .endpoints.auth import router as auth
api.include_router(auth, prefix='/auth')

# multiple request
from .endpoints.multi import router as multi
api.include_router(multi, prefix='/multi')

# 404 error
@api.exception_handler(StarletteHTTPException)
async def _custom_exception_handler(req: Request, exc: StarletteHTTPException):
    options = {
        'code': exc.status_code,
        'method': req.method,
        'path': req.url.path,
    }
    if exc.status_code == 405:
        message = 'Not Found'
        options['code'] = 404
        options['message'] = 'router error'
    else:
        message = 'Invalid Error'
        options['message'] = exc.detail
    return error(message, options)

# validation error
@api.exception_handler(RequestValidationError)
async def _validation_exception_handler(req: Request, exc: RequestValidationError):
    return error('Validation Error', {
        'code': 400,
        'method': req.method,
        'path': req.url.path,
        'error': exc.errors(),
    })

# 예외 처리 핸들러 추가
@api.exception_handler(Exception)
async def _exception_handler(req: Request, e: Exception):
    return error('Exception Error', {
        'code': 500,
        'method': req.method,
        'path': req.url.path,
        'error': e.errors() if hasattr(e, 'errors') else None,
    })
