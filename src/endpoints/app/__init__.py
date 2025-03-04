from fastapi import APIRouter, Form, Query
from . import __types__ as types
from .get_index import get_index
from .get_item import get_item
from .put_item import add_item
from .patch_item import patch_item
from .delete_item import delete_item

# set router
router = APIRouter()

# get apps index
@router.get('/')
async def _index(
    code: str = Query(None),
    name: str = Query(None),
    fields: str = Query(None),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc'),
):
    return await get_index(types.GetIndex(
        code = code,
        name = name,
        fields = fields,
        page = page,
        size = size,
        order = order,
        sort = sort,
    ))

# get app
@router.get('/{srl}/')
async def _item(
    srl: int|str,
    fields: str = Query(None),
):
    return await get_item(types.GetItem(
        srl = srl,
        fields = fields,
    ))

# add app
@router.put('/')
async def _add_item(
    code: str = Form(...),
    name: str = Form(...),
    description: str = Form(None),
):
    return await add_item(types.AddItem(
        code = code,
        name = name,
        description = description,
    ))

# edit app
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    code: str = Form(None),
    name: str = Form(None),
    description: str = Form(None),
):
    return await patch_item(types.PatchItem(
        srl = srl,
        code = code,
        name = name,
        description = description,
    ))

# delete app
@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    return await delete_item(types.DeleteItem(srl = srl))
