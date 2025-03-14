from fastapi import APIRouter, Form, Query
from . import __types__ as types
from .get_index import get_index
from .get_item import get_item
from .put_item import put_item
from .patch_item import patch_item
from .delete_item import delete_item

# set router
router = APIRouter()

# get article index
@router.get('/')
async def _get_index(
    app_srl: int = Query(None, alias='app'),
    nest_srl: int = Query(None, alias='nest'),
    category_srl: int = Query(None, alias='category'),
    q: str = Query(None),
    mode: str = Query(None, pattern=r'^(ready|public|private)$'),
    duration: str = Query(None, pattern=r'^(new|old),(regdate|created_at|updated_at),(day|week|month|year)'),
    random: str = Query(None, pattern=r'^\d{8}$'),
    fields: str = Query(None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$'),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=r'^(asc|desc)$'),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
):
    return await get_index(types.GetIndex(
        app_srl = app_srl,
        nest_srl = nest_srl,
        category_srl = category_srl,
        q = q,
        mode = mode,
        duration = duration,
        random = random,
        fields = fields,
        page = page,
        size = size,
        order = order,
        sort = sort,
        unlimited = unlimited,
    ))

# get article
@router.get('/{srl:int}/')
async def _get_item(
    srl: int,
    fields: str = Query(None, pattern=r'^[a-zA-Z_]+(,[a-zA-Z_]+)*$'),
):
    return await get_item(types.GetItem(
        srl = srl,
        fields = fields,
    ))

# add article
@router.put('/')
async def _put_item():
    return await put_item(types.PutItem())

# edit article
@router.patch('/{srl:int}/')
async def _patch_item(
    srl: int,
    app_srl: int = Form(None, alias='app'),
    nest_srl: int = Form(None, alias='nest'),
    category_srl: int = Form(None, alias='category'),
    title: str = Form(None),
    content: str = Form(None),
    hit: bool = Form(False, convert=lambda v: bool(int(v))),
    star: bool = Form(False, convert=lambda v: bool(int(v))),
    json_data: str = Form(None, alias='json'),
    mode: str = Form(None, pattern=r'^(public|private)$'),
    regdate: str = Form(None, pattern=r'^\d{4}-\d{2}-\d{2}$'),
):
    return await patch_item(types.PatchItem(
        srl = srl,
        app_srl = app_srl,
        nest_srl = nest_srl,
        category_srl = category_srl,
        title = title,
        content = content,
        hit = hit,
        star = star,
        json_data = json_data,
        mode = mode,
        regdate = regdate,
    ))

# delete article
@router.delete('/{srl:int}/')
async def _delete_item(srl: int):
    return await delete_item(types.DeleteItem(srl = srl))
