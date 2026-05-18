from fastapi import APIRouter, Request, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get tags index
@router.get('/')
async def _get_index(
    req: Request,
    module: str = Query(None, pattern=Patterns.tag_module),
    module_srl: int = Query(None),
    name: str = Query(None),
):
    from .get_index import get_index
    return await get_index({
        'module': module,
        'module_srl': module_srl,
        'name': name,
    }, req=req)

# add tag
@router.put('/')
async def _put_item(
    req: Request,
    module: str = Form(..., pattern=Patterns.tag_module),
    module_srl: int = Form(...),
    tags: str = Form(..., pattern=Patterns.tags),
):
    from .put_item import put_item
    return await put_item({
        'module': module,
        'module_srl': module_srl,
        'tags': tags,
    }, req=req)

# update tag
@router.patch('/')
async def _patch_item(
    req: Request,
    module: str = Form(..., pattern=Patterns.tag_module),
    module_srl: int = Form(...),
    tags: str = Form(..., pattern=Patterns.tags),
):
    from .patch_item import patch_item
    return await patch_item({
        'module': module,
        'module_srl': module_srl,
        'tags': tags,
    }, req=req)

# delete tag
@router.delete('/')
async def _delete_item(
    req: Request,
    module: str = Form(..., pattern=Patterns.tag_module),
    module_srl: int = Form(...),
):
    from .delete_item import delete_item
    return await delete_item({
        'module': module,
        'module_srl': module_srl,
    }, req=req)
