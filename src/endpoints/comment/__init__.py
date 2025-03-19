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

# get comment index
@router.get('/')
async def _get_index(
    module: str = Query(None),
    module_srl: int = Query(None),
    content: str = Query(None),
    fields: str = Query(None, pattern=Patterns.fields),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    return await get_index(types.GetIndex(
        module = module,
        module_srl = module_srl,
        content = content,
        fields = fields,
        page = page,
        size = size,
        order = order,
        sort = sort,
        unlimited = unlimited,
    ))

# get comment
@router.get('/{srl:int}/')
async def _get_item(
    srl: int,
    fields: str = Query(None, pattern=Patterns.fields),
):
    return await get_item(types.GetItem(
        srl = srl,
        fields = fields,
    ))

# add comment
@router.put('/')
async def _put_item(
    content: str = Form(...),
    module: str = Form(..., pattern=Patterns.comment_module),
    module_srl: int = Form(...),
):
    return await put_item(types.PutItem(
        content = content,
        module = module,
        module_srl = module_srl,
    ))

# edit comment
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    content: str = Form(None),
    module: str = Form(None, pattern=Patterns.comment_module),
    module_srl: int = Form(None),
):
    return await patch_item(types.PatchItem(
        srl = srl,
        content = content,
        module = module,
        module_srl = module_srl,
    ))

# delete comment
@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    return await delete_item(types.DeleteItem(srl = srl))
