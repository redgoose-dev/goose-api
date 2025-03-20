from fastapi import APIRouter, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types
from .get_index import get_index
from .get_item import get_item
from .put_item import put_item
from .patch_item import patch_item
from .delete_item import delete_item

# set router
router = APIRouter()

# get checklist index
@router.get('/')
async def _get_index(
    content: str = Query(None),
    start: str = Query(None, Patterns=Patterns.date),
    end: str = Query(None, Patterns=Patterns.date),
    fields: str = Query(None, pattern=Patterns.fields),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    return await get_index(types.GetIndex(
        content = content,
        start = start,
        end = end,
        fields = fields,
        page = page,
        size = size,
        order = order,
        sort = sort,
        unlimited = unlimited,
    ))

# get checklist
@router.get('/{srl}/')
async def _get_item(
    srl: int,
    fields: str = Query(None, pattern=Patterns.fields),
):
    return await get_item(types.GetItem(
        srl = srl,
        fields = fields,
    ))

# add checklist
@router.put('/')
async def _put_item(
    content: str = Form(None),
):
    return await put_item(types.PutItem(
        content = content,
    ))

# update checklist
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    content: str = Form(None),
):
    return await patch_item(types.PatchItem(
        srl = srl,
        content = content,
    ))

# get checklist
@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    return await delete_item(types.DeleteItem(
        srl = srl,
    ))
