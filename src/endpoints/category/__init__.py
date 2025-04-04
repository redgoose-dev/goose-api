from fastapi import APIRouter, Request, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get categories index
@router.get('/')
async def _get_index(
    req: Request,
    fields: str = Query(None, pattern=Patterns.fields),
    name: str = Query(None),
    module: str = Query(None, pattern=Patterns.category_module),
    module_srl: int = Query(None),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
    mod: str = Query(None, pattern=Patterns.mod),
    q: str = Query(None),
):
    from .get_index import get_index
    return await get_index({
        'fields': fields,
        'name': name,
        'module': module,
        'module_srl': module_srl,
        'page': page,
        'size': size,
        'order': order,
        'sort': sort,
        'unlimited': unlimited,
        'mod': mod,
        'q': q,
    }, req=req)

# get category
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

# add category
@router.put('/')
async def _put_item(
    req: Request,
    name: str = Form(...),
    module: str = Form(..., pattern=Patterns.category_module),
    module_srl: int = Form(None),
):
    from .put_item import put_item
    return await put_item({
        'name': name,
        'module': module,
        'module_srl': module_srl,
    }, req=req)

# edit category
@router.patch('/{srl:int}/')
async def _patch_item(
    req: Request,
    srl: int,
    name: str = Form(None),
    module: str = Form(None, pattern=Patterns.category_module),
    module_srl: int = Form(None),
):
    from .patch_item import patch_item
    return await patch_item({
        'srl': srl,
        'name': name,
        'module': module,
        'module_srl': module_srl,
    }, req=req)

# change order
@router.patch('/change-order/')
async def _patch_change_order(
    req: Request,
    module: str = Form(..., pattern=Patterns.category_module),
    module_srl: int = Form(...),
    srls: str = Form(..., pattern=Patterns.srls), # 1,2,3
):
    from .patch_change_order import patch_change_order
    return await patch_change_order({
        'module': module,
        'module_srl': module_srl,
        'srls': srls,
    }, req=req)

# delete category
@router.delete('/{srl:int}/')
async def _delete_item(
    req: Request,
    srl: int,
):
    from .delete_item import delete_item
    return await delete_item({
        'srl': srl,
    }, req=req)
