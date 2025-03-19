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

# get nests index
@router.get('/')
async def _get_index(
    fields: str = Query(None, pattern=Patterns.fields),
    app_srl: int = Query(None, alias='app'),
    code: str = Query(None, pattern=Patterns.code),
    name: str = Query(None),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    return await get_index(types.GetIndex(
        fields = fields,
        app_srl = app_srl,
        code = code,
        name = name,
        page = page,
        size = size,
        order = order,
        sort = sort,
        unlimited = unlimited,
    ))

# get nest
@router.get('/{srl}/')
async def _get_item(
    srl: int|str,
    fields: str = Query(None, pattern=Patterns.fields),
):
    return await get_item(types.GetItem(
        srl = srl,
        fields = fields,
    ))

# get nest
@router.put('/')
async def _put_item(
    app_srl: int = Form(..., alias='app'),
    code: str = Form(..., pattern=Patterns.code),
    name: str = Form(...),
    description: str = Form(None),
    json_data: str = Form('{}', alias='json'),
):
    return await put_item(types.PutItem(
        app_srl = app_srl,
        code = code,
        name = name,
        description = description,
        json_data = json_data,
    ))

# get nest
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    app_srl: int = Form(None, alias='app'),
    code: str = Form(None, pattern=Patterns.code),
    name: str = Form(None),
    description: str = Form(None),
    json_data: str = Form(None, alias='json')
):
    return await patch_item(types.PatchItem(
        srl = srl,
        app_srl = app_srl,
        code = code,
        name = name,
        description = description,
        json_data = json_data,
    ))

# get nest
@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    return await delete_item(types.DeleteItem(
        srl = srl,
    ))
