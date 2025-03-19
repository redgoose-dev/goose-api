from fastapi import APIRouter, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types
from .get_index import get_index
from .get_item import get_item
from .put_item import put_item
from .patch_item import patch_item
from .delete_item import delete_item

# set router
router = APIRouter()

# get apps index
@router.get('/')
async def _index(
    code: str = Query(None),
    name: str = Query(None),
    fields: str = Query(None, pattern=Patterns.fields),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.fields),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    return await get_index(types.GetIndex(
        code = code,
        name = name,
        fields = fields,
        page = page,
        size = size,
        order = order,
        sort = sort,
        unlimited = unlimited,
    ))

# get app
@router.get('/{srl}/')
async def _item(
    srl: int|str,
    fields: str = Query(None, pattern=Patterns.fields),
):
    return await get_item(types.GetItem(
        srl = srl,
        fields = fields,
    ))

# add app
@router.put('/')
async def _put_item(
    code: str = Form(..., pattern=Patterns.code),
    name: str = Form(...),
    description: str = Form(None),
):
    return await put_item(types.AddItem(
        code = code,
        name = name,
        description = description,
    ))

# edit app
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    code: str = Form(None, pattern=Patterns.code),
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
