from fastapi import APIRouter, Request, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get checklist index
@router.get('/')
async def _get_index(
    req: Request,
    content: str = Query(None),
    start: str = Query(None, Patterns=Patterns.date),
    end: str = Query(None, Patterns=Patterns.date),
    fields: str = Query(None, pattern=Patterns.fields),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
    tag: str = Query(None, pattern=Patterns.tags),
):
    from .get_index import get_index
    return await get_index({
        'content': content,
        'start': start,
        'end': end,
        'fields': fields,
        'page': page,
        'size': size,
        'order': order,
        'sort': sort,
        'unlimited': unlimited,
        'tag': tag,
    }, req=req)

# get checklist
@router.get('/{srl}/')
async def _get_item(
    req: Request,
    srl: int,
    fields: str = Query(None, pattern=Patterns.fields),
):
    from .get_item import get_item
    return await get_item({
        'srl': srl,
        'fields': fields,
    }, req=req)

# add checklist
@router.put('/')
async def _put_item(
    req: Request,
    content: str = Form(None),
    tag: str = Form(None, pattern=Patterns.tags),
):
    from .put_item import put_item
    return await put_item({
        'content': content,
        'tag': tag,
    }, req=req)

# update checklist
@router.patch('/{srl:int}/')
async def _patch_item(
    req: Request,
    srl: int,
    content: str = Form(None),
    tag: str = Form(None, pattern=Patterns.tags),
):
    from .patch_item import patch_item
    return await patch_item({
        'srl': srl,
        'content': content,
        'tag': tag,
    }, req=req)

# get checklist
@router.delete('/{srl:int}/')
async def _delete_item(
    req: Request,
    srl: int,
):
    from .delete_item import delete_item
    return await delete_item({
        'srl': srl,
    }, req=req)
