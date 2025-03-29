from fastapi import APIRouter, Request, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get comment index
@router.get('/')
async def _get_index(
    req: Request,
    module: str = Query(None, pattern=Patterns.comment_module),
    module_srl: int = Query(None),
    content: str = Query(None),
    fields: str = Query(None, pattern=Patterns.fields),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    from .get_index import get_index
    return await get_index({
        'module': module,
        'module_srl': module_srl,
        'content': content,
        'fields': fields,
        'page': page,
        'size': size,
        'order': order,
        'sort': sort,
        'unlimited': unlimited,
    }, req=req)

# get comment
@router.get('/{srl:int}/')
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

# add comment
@router.put('/')
async def _put_item(
    req: Request,
    content: str = Form(...),
    module: str = Form(..., pattern=Patterns.comment_module),
    module_srl: int = Form(...),
):
    from .put_item import put_item
    return await put_item({
        'content': content,
        'module': module,
        'module_srl': module_srl,
    }, req=req)

# edit comment
@router.patch('/{srl:int}/')
async def _patch_item(
    req: Request,
    srl: int,
    content: str = Form(None),
    module: str = Form(None, pattern=Patterns.comment_module),
    module_srl: int = Form(None),
):
    from .patch_item import patch_item
    return await patch_item({
        'srl': srl,
        'content': content,
        'module': module,
        'module_srl': module_srl,
    }, req=req)

# delete comment
@router.delete('/{srl:int}/')
async def _delete_item(
    req: Request,
    srl: int,
):
    from .delete_item import delete_item
    return await delete_item({
        'srl': srl,
    }, req=req)
