from fastapi import APIRouter
from starlette.responses import Response
from .endpoints.options_any import preflight
from .endpoints.get_home import home

# set router
router = APIRouter()

# preflight
@router.options('/{path_str:path}')
async def _options(path_str: str) -> Response:
    return await preflight(path_str)

# home
@router.get('/')
def _home():
    return home()
