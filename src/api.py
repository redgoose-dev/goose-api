import os, time
from datetime import datetime
from fastapi import FastAPI, Request
from fastapi.responses import Response, JSONResponse
from fastapi.exceptions import RequestValidationError
from starlette.exceptions import HTTPException as StarletteHTTPException
from .libs.string import color_text
from .output import error
from .endpoints.options_any import preflight
from .endpoints.get_home import home
from .endpoints.app import router as app
from .endpoints.json import router as json
from .endpoints.category import router as category
from .endpoints.nest import router as nest

# set router
api = FastAPI()

# debug
__DEBUG__ = os.getenv('DEBUG', '').lower() == 'true'

# middleware - checking process time
@api.middleware('http')
async def add_process_time_header(req: Request, call_next):
    now = None
    start_time = time.time()
    if __DEBUG__:
        now = datetime.now().strftime('%H:%M:%S')
        print(color_text(f'=== ACTION START {now} ======================', 'cyan'))
    response = await call_next(req)
    process_time = (time.time() - start_time) * 1000
    response.headers['X-Process-Time'] = f'{process_time:.2f} ms'
    if __DEBUG__:
        print(color_text(f'=== ACTION END {now} ========================', 'cyan'))
    return response

# preflight
@api.options('/{path_str:path}')
async def _options(path_str: str) -> Response:
    return await preflight(path_str)

# home
@api.get('/')
def _home() -> JSONResponse:
    return home()

# app
api.include_router(app, prefix='/app')

# article
# api.include_router(article, prefix='/article')

# auth

# category

# checklist
api.include_router(category, prefix='/category')

# comment

# file

# json
api.include_router(json, prefix='/json')

# nest
api.include_router(nest, prefix='/nest')

# 404
@api.exception_handler(StarletteHTTPException)
async def custom_http_exception_handler(req: Request, exc: StarletteHTTPException):
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
async def validation_exception_handler(req: Request, exc: RequestValidationError):
    return error('Validation Error', {
        'code': 422,
        'method': req.method,
        'path': req.url.path,
        'error': exc.errors(),
    })

# 예외 처리 핸들러 추가
@api.exception_handler(Exception)
async def global_exception_handler(req: Request, exc: Exception):
    return error('Exception Error', {
        'code': 500,
        'method': req.method,
        'path': req.url.path,
        'error': exc.errors() if hasattr(exc, 'errors') else None,
    })
