from fastapi import APIRouter, Request, Form, Query, File, UploadFile
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# TODO: 이미지 파일 컨버트. webp,avif 포맷 지원, 퀄리티 조절가능, 리사이즈는 고민 필요함

# get files index
@router.get('/')
async def _get_index(
    req: Request,
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
    from .get_index import get_index
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
    ), req = req)

# get file
@router.get('/{srl}/')
async def _get_item(
    req: Request,
    srl: int|str,
):
    from .get_item import get_item
    return await get_item(types.GetItem(
        srl = srl,
    ), req = req)

# add file
@router.put('/')
async def _put_item(
    req: Request,
    module: str = Form(..., pattern=Patterns.file_modules),
    module_srl: int = Form(...),
    file: UploadFile = File(...),
    json_data: str = Form(default='{}', alias='json'),
):
    from .put_item import put_item
    return await put_item(types.PutItem(
        module = module,
        module_srl = module_srl,
        file = file,
        json_data = json_data,
    ), req = req)

# edit file
@router.patch('/{srl:int}/')
async def _patch_item(
    req: Request,
    srl: int,
    module: str = Form(None, pattern=Patterns.file_modules),
    module_srl: int = Form(None),
    json_data: str = Form(None, alias='json'),
    file: UploadFile = File(None),
):
    from .patch_item import patch_item
    return await patch_item(types.PatchItem(
        srl = srl,
        module = module,
        module_srl = module_srl,
        json_data = json_data,
        file = file,
    ), req = req)

# delete file
@router.delete('/{srl:int}/')
async def _delete_item(
    req: Request,
    srl: int,
):
    from .delete_item import delete_item
    return await delete_item(types.DeleteItem(
        srl = srl,
    ), req = req)
