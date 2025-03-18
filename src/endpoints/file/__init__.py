from fastapi import APIRouter, Form, Query, File, UploadFile
from . import __types__ as types
from src.libs.resource import Patterns
from .get_index import get_index
from .get_item import get_item
from .put_item import put_item
from .patch_item import patch_item
from .delete_item import delete_item

# set router
router = APIRouter()

# get files index
@router.get('/')
async def _get_index(
    fields: str = Query(None, pattern=Patterns.fields),
    module: str = Query(None, pattern=Patterns.file_modules),
    module_srl: int = Query(None),
    name: str = Query(None),
    mime: str = Query(None),
    page: int = Query(default=1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query(default='srl'),
    sort: str = Query(default='desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    return await get_index(types.GetIndex(
        fields = fields,
        module = module,
        module_srl = module_srl,
        name = name,
        mime = mime,
        page = page,
        size = size,
        order = order,
        sort = sort,
        unlimited = unlimited,
    ))

# get file
@router.get('/{srl}/')
async def _get_item(srl: int|str):
    return await get_item(types.GetItem(srl = srl))

# add file
@router.put('/')
async def _put_item(
    module: str = Form(..., pattern=Patterns.file_modules),
    module_srl: int = Form(...),
    file: UploadFile = File(...),
    json_data: str = Form(default='{}', alias='json'),
):
    return await put_item(types.PutItem(
        module = module,
        module_srl = module_srl,
        file = file,
        json_data = json_data,
    ))

# edit file
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    module: str = Form(None, pattern=Patterns.file_modules),
    module_srl: int = Form(None),
    json_data: str = Form(None, alias='json'),
    file: UploadFile = File(None),
):
    return await patch_item(types.PatchItem(
        srl = srl,
        module = module,
        module_srl = module_srl,
        json_data = json_data,
        file = file,
    ))

# delete file
@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    return await delete_item(types.DeleteItem(srl = srl))
