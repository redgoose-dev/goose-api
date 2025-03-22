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
from .endpoints.article import router as article
from .endpoints.json import router as json
from .endpoints.category import router as category
from .endpoints.nest import router as nest
from .endpoints.file import router as file
from .endpoints.comment import router as comment
from .endpoints.checklist import router as checklist
from .endpoints.auth import router as auth

# docs
# - Request Class: https://fastapi.tiangolo.com/ko/reference/request/?h=request#fastapi.Request

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
        print(color_text(f'\n***** | START | {now} | [{req.method}] {req.url} | *****', 'cyan'))
    response = await call_next(req)
    process_time = (time.time() - start_time) * 1000
    response.headers['X-Process-Time'] = f'{process_time:.2f} ms'
    if __DEBUG__:
        print(color_text(f'***** | END | {now} | *****', 'cyan'))
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
api.include_router(article, prefix='/article')

# category
api.include_router(category, prefix='/category')

# checklist
api.include_router(checklist, prefix='/checklist')

# comment
api.include_router(comment, prefix='/comment')

# file
api.include_router(file, prefix='/file')

# json
api.include_router(json, prefix='/json')

# nest
api.include_router(nest, prefix='/nest')

# auth
api.include_router(auth, prefix='/auth')

# multiple request
@api.post('/multi/')
async def _multi():
    # TODO: 여러가지 요청을 한번에 처리하는 API
    # TODO: 배열로 요청들을 받고, 각각의 요청을 처리한 후 결과를 배열로 반환
    # TODO: 체인으로 연결된 요청을 처리할 수 있도록 구현
    # TODO: 0번 요청을 처리하여 나온 리턴을 1번에서 값을 받아서 처리할 수 있는지 연구 필요하다.
    print('multi=====>')
    pass

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
        'code': 400,
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
