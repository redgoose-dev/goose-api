from fastapi import APIRouter, Request, Form, Query, File, UploadFile
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get files index
@router.get('/')
async def _get_index(
    req: Request,
    fields: str = Query(None, pattern=Patterns.fields),
    module: str = Query(None, pattern=Patterns.file_modules),
    module_srl: int = Query(None),
    name: str = Query(None),
    mime: str = Query(None, pattern=Patterns.file_mime),
    page: int = Query(default=1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query(default='srl'),
    sort: str = Query(default='desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    from .get_index import get_index
    return await get_index({
        'fields': fields,
        'module': module,
        'module_srl': module_srl,
        'name': name,
        'mime': mime,
        'page': page,
        'size': size,
        'order': order,
        'sort': sort,
        'unlimited': unlimited,
    }, req=req)

# get file
@router.get('/{srl}/')
async def _get_item(
    req: Request,
    srl: int|str, # srl or code
    w: int = Query(None), # width
    h: int = Query(None), # height
    t: str = Query(None), # fit
    q: int = Query(None), # quality
):
    from .get_item import get_item
    return await get_item({
        'srl': srl,
        'w': w,
        'h': h,
        't': t,
        'q': q,
    }, req=req)

# add file
@router.put('/')
async def _put_item(
    req: Request,
    module: str = Form(..., pattern=Patterns.file_modules),
    module_srl: int = Form(...),
    file: UploadFile = File(...),
    json_data: str = Form(default='{}', alias='json'),
    file_format: str = Form(None, alias='format'), # image/webp,image/avif
    file_quality: int = Form(90, gt=1, le=100, alias='quality'), # 0 ~ 100
):
    from .put_item import put_item
    return await put_item({
        'module': module,
        'module_srl': module_srl,
        'file': file,
        'json_data': json_data,
        'file_format': file_format,
        'file_quality': file_quality,
    }, req=req)

# edit file
@router.patch('/{srl:int}/')
async def _patch_item(
    req: Request,
    srl: int,
    module: str = Form(None, pattern=Patterns.file_modules),
    module_srl: int = Form(None),
    json_data: str = Form(None, alias='json'),
    file: UploadFile = File(None),
    file_format: str = Form(None, alias='format'), # image/webp,image/avif
    file_quality: int = Form(95, gt=1, le=100, alias='quality'), # 0 ~ 100
):
    from .patch_item import patch_item
    return await patch_item({
        'srl': srl,
        'module': module,
        'module_srl': module_srl,
        'json_data': json_data,
        'file': file,
        'file_format': file_format,
        'file_quality': file_quality,
    }, req=req)

# delete file
@router.delete('/{srl:int}/')
async def _delete_item(
    req: Request,
    srl: int,
):
    from .delete_item import delete_item
    return await delete_item({
        'srl': srl,
    }, req=req)
