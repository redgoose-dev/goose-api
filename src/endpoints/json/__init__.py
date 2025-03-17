from fastapi import APIRouter, Form, Query
from . import __types__ as types
from .get_index import get_index
from .get_item import get_item
from .put_item import put_item
from .patch_item import patch_item
from .delete_item import delete_item

# set router
router = APIRouter()

# get json index
@router.get('/')
async def _get_index(
    name: str = Query(None),
    category_srl: int = Query(None, alias='category'),
    fields: str = Query(None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$'),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=r'^(asc|desc)$'),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    return await get_index(types.GetIndex(
        name = name,
        category_srl = category_srl,
        fields = fields,
        page = page,
        size = size,
        order = order,
        sort = sort,
        unlimited = unlimited,
    ))

# get json
@router.get('/{srl:int}/')
async def _get_item(
    srl: int,
    fields: str = Query(None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$'),
):
    return await get_item(types.GetItem(
        srl = srl,
        fields = fields,
    ))

# add json
@router.put('/')
async def _put_item(
    category_srl: int = Form(None, alias='category'),
    name: str = Form(...),
    description: str = Form(None),
    json_data: str = Form(..., alias='json'),
    path: str = Form(None),
):
    return await put_item(types.PutItem(
        category_srl = category_srl,
        name = name,
        description = description,
        json_data = json_data,
        path = path,
    ))

# edit json
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    category_srl: int = Form(None, alias='category'),
    name: str = Form(None),
    description: str = Form(None),
    json_data: str = Form(None, alias='json'),
    path: str = Form(None),
):
    return await patch_item(types.PatchItem(
        srl = srl,
        category_srl = category_srl,
        name = name,
        description = description,
        json_data = json_data,
        path = path,
    ))

# delete json
@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    return await delete_item(types.DeleteItem(srl = srl))
