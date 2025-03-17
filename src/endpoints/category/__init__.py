from fastapi import APIRouter, Form, Query
from . import __types__ as types
from .get_index import get_index
from .get_item import get_item
from .put_item import put_item
from .patch_item import patch_item
from .patch_change_order import patch_change_order
from .delete_item import delete_item

# set router
router = APIRouter()

# get categories index
@router.get('/')
async def _get_index(
    fields: str = Query(None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$'),
    name: str = Query(None),
    module: str = Query(None, pattern=r'^(nest|json)$'),
    module_srl: int = Query(None),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=r'^(asc|desc)$'),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    return await get_index(types.GetIndex(
        fields = fields,
        name = name,
        module = module,
        module_srl = module_srl,
        page = page,
        size = size,
        order = order,
        sort = sort,
        unlimited = unlimited,
    ))

# get category
@router.get('/{srl:int}/')
async def _get_item(
    srl: int,
    fields: str = Query(None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$'),
):
    return await get_item(types.GetItem(
        srl = srl,
        fields = fields,
    ))

# add category
@router.put('/')
async def _put_item(
    name: str = Form(...),
    module: str = Form(..., pattern=r'^(nest|json)$'),
    module_srl: int = Form(None),
):
    return await put_item(types.PutItem(
        name = name,
        module = module,
        module_srl = module_srl,
    ))

# edit category
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    name: str = Form(None),
    module: str = Form(None, pattern=r'^(nest|json)$'),
    module_srl: int = Form(None),
):
    return await patch_item(types.PatchItem(
        srl = srl,
        name = name,
        module = module,
        module_srl = module_srl,
    ))

# change order
@router.patch('/change-order/')
async def _patch_change_order(
    module: str = Form(..., pattern=r'^(nest|json)$'),
    module_srl: int = Form(...),
    srls: str = Form(..., pattern=r'^\d+(,\d+)*$'), # 1,2,3
):
    return await patch_change_order(types.PatchChangeOrder(
        module = module,
        module_srl = module_srl,
        srls = srls,
    ))

# delete category
@router.delete('/{srl:int}/')
async def _delete_item(
    srl: int,
):
    return await delete_item(types.DeleteItem(
        srl = srl,
    ))
