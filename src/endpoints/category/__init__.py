from fastapi import APIRouter, Form, Query
from . import __types__ as types
from .get_index import get_index
from .put_item import put_item
from .patch_item import patch_item
from .patch_change_order import patch_change_order
from .delete_item import delete_item

# set router
router = APIRouter()

# TODO: 테스트와 개선작업 필요함

# get categories index
@router.get('/')
async def _get_index(
    fields: str = Query(None),
    name: str = Query(None),
    module: str = Query(None),
    target_srl: int = Query(None),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc'),
):
    return await get_index(types.GetIndex(
        fields = fields,
        name = name,
        module = module,
        target_srl = target_srl,
        page = page,
        size = size,
        order = order,
        sort = sort,
    ))

# add category
@router.put('/')
async def _put_item(
    name: str = Form(...),
    module: str = Form(...),
    target_srl: int = Form(None),
):
    return await put_item(types.PutItem(
        name = name,
        module = module,
        target_srl = target_srl,
    ))

# edit category
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    target_srl: int = Form(None),
    name: str = Form(None),
    module: str = Form(None),
):
    return await patch_item(types.PatchItem(
        srl = srl,
        target_srl = target_srl,
        name = name,
        module = module,
    ))

# change order
@router.patch('/{srl:int}/change-order/')
async def _patch_change_order(
    srl: int,
    order: str = Form(...),
):
    return await patch_change_order(types.PatchChangeOrder(
        srl = srl,
        order = order,
    ))

# delete category
@router.delete('/{srl:int}/')
async def _delete_item(
    srl: int,
):
    return await delete_item(types.DeleteItem(
        srl = srl,
    ))
