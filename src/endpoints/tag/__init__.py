from fastapi import APIRouter, Request, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get tags index
@router.get('/')
async def _get_index(
    req: Request,
):
    # TODO: 추가할 항목 - module, module_srl, name
    from .get_index import get_index
    return await get_index({}, req=req)
