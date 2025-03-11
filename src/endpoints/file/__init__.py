from fastapi import APIRouter, Form, Query, File, UploadFile
from . import __types__ as types
from .get_index import get_index
from .get_item import get_item
from .put_item import put_item
from .patch_item import patch_item
from .delete_item import delete_item

# TODO: 테스트와 개선작업 필요함

# set router
router = APIRouter()

# patterns
patterns = {
    'module': r'^(article|json|checklist)$',
    'fields': r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$',
    'sort': r'^(asc|desc)$'
}

# get files index
@router.get('/')
async def _get_index(
    fields: str = Query(None, pattern=patterns['fields']),
    module: str = Query(None, pattern=patterns['module']),
    module_srl: int = Query(None),
    name: str = Query(None),
    mime: str = Query(None),
    page: int = Query(default=1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query(default='srl'),
    sort: str = Query(default='desc', pattern=patterns['sort']),
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
    ))

# get file
@router.get('/{srl}/')
async def _get_item(srl: int|str):
    return await get_item(types.GetItem(srl = srl))

# add file
@router.put('/')
async def _put_item(
    module: str = Form(..., pattern=patterns['module']),
    module_srl: int = Form(...),
    json_data: str = Form(default='{}', alias='json'),
    file: UploadFile = File(...),
):
    return await put_item(types.PutItem(
        module = module,
        module_srl = module_srl,
        json_data = json_data,
        file = file,
    ))

# edit file
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    module: str = Form(None, pattern=patterns['module']),
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
