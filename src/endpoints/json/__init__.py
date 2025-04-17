from fastapi import APIRouter, Request, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get json index
@router.get('/')
async def _get_index(
    req: Request,
    category_srl: int = Query(None, alias='category'),
    name: str = Query(None),
    fields: str = Query(None, pattern=Patterns.fields),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
    tag: str = Query(None, pattern=Patterns.tags),
    mod: str = Query(None, pattern=Patterns.mod),
):
    from .get_index import get_index
    return await get_index({
        'category_srl': category_srl,
        'name': name,
        'fields': fields,
        'page': page,
        'size': size,
        'order': order,
        'sort': sort,
        'unlimited': unlimited,
        'tag': tag,
        'mod': mod,
    }, req=req)

# get json
@router.get('/{srl:int}/')
async def _get_item(
    req: Request,
    srl: int,
    fields: str = Query(None, pattern=Patterns.fields),
    mod: str = Query(None, pattern=Patterns.mod),
):
    from .get_item import get_item
    return await get_item({
        'srl': srl,
        'fields': fields,
        'mod': mod,
    }, req=req)

# add json
@router.put('/')
async def _put_item(
    req: Request,
    category_srl: int = Form(None, alias='category'),
    name: str = Form(...),
    description: str = Form(None),
    json_data: str = Form(..., alias='json'),
    tag: str = Form(None, pattern=Patterns.tags),
):
    from .put_item import put_item
    return await put_item({
        'category_srl': category_srl,
        'name': name,
        'description': description,
        'json_data': json_data,
        'tag': tag,
    }, req=req)

# edit json
@router.patch('/{srl:int}/')
async def _patch_item(
    req: Request,
    srl: int,
    category_srl: int = Form(None, alias='category'),
    name: str = Form(None),
    description: str = Form(None),
    json_data: str = Form(None, alias='json'),
    tag: str = Form(None, pattern=Patterns.tags),
):
    from .patch_item import patch_item
    return await patch_item({
        'srl': srl,
        'category_srl': category_srl,
        'name': name,
        'description': description,
        'json_data': json_data,
        'tag': tag,
    }, req=req)

# delete json
@router.delete('/{srl:int}/')
async def _delete_item(
    req: Request,
    srl: int,
):
    from .delete_item import delete_item
    return await delete_item({
        'srl': srl,
    }, req=req)
