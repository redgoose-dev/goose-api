from fastapi import APIRouter, Form, Query, File, UploadFile
from . import __types__ as types
from .get_index import get_index
from .get_item import get_item
from .put_item import put_item
from .delete_item import delete_item
from .patch_item import patch_item

# set router
router = APIRouter()

# get files index
@router.get('/')
async def _get_index(
    fields: str = Query(None),
    module: str = Query(None),
    target_srl: int = Query(None, alias='target'),
    name: str = Query(None),
    type: str = Query(None),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc'),
):
    return await get_index(types.GetIndex(
        fields = fields,
        module = module,
        target_srl = target_srl,
        name = name,
        type = type,
        page = page,
        size = size,
        order = order,
        sort = sort,
    ))

# get file
@router.get('/{srl}/')
async def _get_item(srl: int|str):
    return await get_item(types.GetItem(srl = srl))

# add file
@router.put('/')
async def _put_item(
    target_srl: int = Form(..., alias='target'),
    module: str = Form(...),
    json_data: str = Form(None, alias='json'),
    file: UploadFile = File(...),
):
    return await put_item(types.PutItem(
        target_srl = target_srl,
        module = module,
        json_data = json_data,
        file = file,
    ))

# edit file
@router.patch('/{srl:int}/')
async def _put_item(
    srl: int,
    target_srl: int = Form(None, alias='target'),
    module: str = Form(None),
    json_data: str = Form(None, alias='json'),
    file: UploadFile = File(None),
):
    return await patch_item(types.PatchItem(
        srl = srl,
        target_srl = target_srl,
        module = module,
        json_data = json_data,
        file = file,
    ))

# delete file
@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    return await delete_item(types.DeleteItem(srl = srl))
