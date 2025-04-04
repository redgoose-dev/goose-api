from fastapi import APIRouter, Request, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get tags index
@router.get('/')
async def _get_index(
    req: Request,
    module: str = Query(None, pattern=Patterns.tag_module),
    module_srl: int = Query(None),
    name: str = Query(None),
):
    from .get_index import get_index
    return await get_index({
        'module': module,
        'module_srl': module_srl,
        'name': name,
    }, req=req)
