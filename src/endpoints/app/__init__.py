from fastapi import Request, APIRouter, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get apps index
@router.get('/')
async def _get_index(
    req: Request,
    code: str = Query(None),
    name: str = Query(None),
    fields: str = Query(None, pattern=Patterns.fields),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.fields),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    from .get_index import get_index
    return await get_index({
        'code': code,
        'name': name,
        'fields': fields,
        'page': page,
        'size': size,
        'order': order,
        'sort': sort,
        'unlimited': unlimited,
    }, req=req)

# get app
@router.get('/{srl}/')
async def _get_item(
    req: Request,
    srl: int|str,
    fields: str = Query(None, pattern=Patterns.fields),
):
    from .get_item import get_item
    return await get_item({
        'srl': srl,
        'fields': fields,
    }, req=req)

# add app
@router.put('/')
async def _put_item(
    req: Request,
    code: str = Form(..., pattern=Patterns.code),
    name: str = Form(...),
    description: str = Form(None),
):
    from .put_item import put_item
    return await put_item({
        'code': code,
        'name': name,
        'description': description,
    }, req=req)

# edit app
@router.patch('/{srl:int}/')
async def _patch_item(
    req: Request,
    srl: int,
    code: str = Form(None, pattern=Patterns.code),
    name: str = Form(None),
    description: str = Form(None),
):
    from .patch_item import patch_item
    return await patch_item({
        'srl': srl,
        'code': code,
        'name': name,
        'description': description,
    }, req=req)

# delete app
@router.delete('/{srl:int}/')
async def _delete_item(
    req: Request,
    srl: int
):
    from .delete_item import delete_item
    return await delete_item({
        'srl': srl,
    }, req=req)
