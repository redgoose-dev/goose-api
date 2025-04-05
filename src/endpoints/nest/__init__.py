from fastapi import APIRouter, Request, Form, Query
from src.libs.resource import Patterns

# set router
router = APIRouter()

# get nests index
@router.get('/')
async def _get_index(
    req: Request,
    fields: str = Query(None, pattern=Patterns.fields),
    app_srl: int = Query(None, alias='app'),
    code: str = Query(None, pattern=Patterns.code),
    name: str = Query(None),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
    mod: str = Query(None, pattern=Patterns.mod),
):
    from .get_index import get_index
    return await get_index({
        'fields': fields,
        'app_srl': app_srl,
        'code': code,
        'name': name,
        'page': page,
        'size': size,
        'order': order,
        'sort': sort,
        'unlimited': unlimited,
        'mod': mod,
    }, req=req)

# get nest
@router.get('/{srl}/')
async def _get_item(
    req: Request,
    srl: int|str,
    fields: str = Query(None, pattern=Patterns.fields),
    mod: str = Query(None, pattern=Patterns.mod),
):
    from .get_item import get_item
    return await get_item({
        'srl': srl,
        'fields': fields,
        'mod': mod,
    }, req=req)

# add nest
@router.put('/')
async def _put_item(
    req: Request,
    app_srl: int = Form(..., alias='app'),
    code: str = Form(..., pattern=Patterns.code),
    name: str = Form(...),
    description: str = Form(None),
    json_data: str = Form('{}', alias='json'),
):
    from .put_item import put_item
    return await put_item({
        'app_srl': app_srl,
        'code': code,
        'name': name,
        'description': description,
        'json_data': json_data,
    }, req=req)

# update nest
@router.patch('/{srl:int}/')
async def _patch_item(
    req: Request,
    srl: int,
    app_srl: int = Form(None, alias='app'),
    code: str = Form(None, pattern=Patterns.code),
    name: str = Form(None),
    description: str = Form(None),
    json_data: str = Form(None, alias='json')
):
    from .patch_item import patch_item
    return await patch_item({
        'srl': srl,
        'app_srl': app_srl,
        'code': code,
        'name': name,
        'description': description,
        'json_data': json_data,
    }, req=req)

# delete nest
@router.delete('/{srl:int}/')
async def _delete_item(
    req: Request,
    srl: int,
):
    from .delete_item import delete_item
    return await delete_item({
        'srl': srl,
    }, req=req)
