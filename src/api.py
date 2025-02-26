import time
from fastapi import FastAPI, Request
from fastapi.responses import Response, JSONResponse
from fastapi.exceptions import RequestValidationError
from starlette.exceptions import HTTPException as StarletteHTTPException
from .output import error
from .endpoints.options_any import preflight
from .endpoints.get_home import home
from .endpoints.app import router as app

# set router
api = FastAPI()

# middleware - checking process time
@api.middleware('http')
async def add_process_time_header(req: Request, call_next):
    start_time = time.time()
    response = await call_next(req)
    process_time = (time.time() - start_time) * 1000
    response.headers['X-Process-Time'] = f'{process_time:.2f} ms'
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

# comment

# file

# json

# nest

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


@api.exception_handler(RequestValidationError)
async def validation_exception_handler(req: Request, exc: RequestValidationError):
    return error('Validation Error', {
        'code': 422,
        'method': req.method,
        'path': req.url.path,
        'error': exc.errors(),
    })
