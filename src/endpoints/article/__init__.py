from fastapi import Request, APIRouter, Form, Query
from src.libs.resource import Patterns
from . import __types__ as types

# set router
router = APIRouter()

# get article index
@router.get('/')
async def _get_index(
    req: Request,
    app_srl: int = Query(None, alias='app'),
    nest_srl: int = Query(None, alias='nest'),
    category_srl: int = Query(None, alias='category'),
    q: str = Query(None),
    mode: str = Query(None, pattern=Patterns.article_mode),
    duration: str = Query(None, pattern=Patterns.article_duration),
    random: str = Query(None, pattern=Patterns.article_random),
    fields: str = Query(None, pattern=Patterns.fields),
    page: int = Query(1, gt=0),
    size: int = Query(None, gt=0),
    order: str = Query('srl'),
    sort: str = Query('desc', pattern=Patterns.sort),
    unlimited: bool = Query(False, convert=lambda v: bool(int(v)) if v else False),
    tag: str = Query(None, pattern=Patterns.tags),
    mod: str = Query(None, pattern=Patterns.mod),
):
    from .get_index import get_index
    return await get_index({
        'app_srl': app_srl,
        'nest_srl': nest_srl,
        'category_srl': category_srl,
        'q': q,
        'mode': mode,
        'duration': duration,
        'random': random,
        'fields': fields,
        'page': page,
        'size': size,
        'order': order,
        'sort': sort,
        'unlimited': unlimited,
        'tag': tag,
        'mod': mod,
    }, req=req)

# get article
@router.get('/{srl:int}/')
async def _get_item(
    req: Request,
    srl: int,
    fields: str = Query(None, pattern=Patterns.fields),
    mod: str = Query(None, pattern=Patterns.mod),
):
    from .get_item import get_item
    return await get_item({
        'srl': srl,
        'fields': fields,
        'mod': mod,
    }, req=req)

# add article
@router.put('/')
async def _put_item(req: Request):
    from .put_item import put_item
    return await put_item(req=req)

# edit article
@router.patch('/{srl:int}/')
async def _patch_item(
    req: Request,
    srl: int,
    app_srl: int = Form(None, alias='app'),
    nest_srl: int = Form(None, alias='nest'),
    category_srl: int = Form(None, alias='category'),
    title: str = Form(None),
    content: str = Form(None),
    hit: bool = Form(False, convert=lambda v: bool(int(v))),
    star: bool = Form(False, convert=lambda v: bool(int(v))),
    json_data: str = Form(None, alias='json'),
    mode: str = Form(None, pattern=Patterns.article_mode),
    regdate: str = Form(None, pattern=Patterns.date),
    tag: str = Form(None, pattern=Patterns.tags),
):
    from .patch_item import patch_item
    return await patch_item({
        'srl': srl,
        'app_srl': app_srl,
        'nest_srl': nest_srl,
        'category_srl': category_srl,
        'title': title,
        'content': content,
        'hit': hit,
        'star': star,
        'json_data': json_data,
        'mode': mode,
        'regdate': regdate,
        'tag': tag,
    }, req=req)

# delete article
@router.delete('/{srl:int}/')
async def _delete_item(
    req: Request,
    srl: int,
):
    from .delete_item import delete_item
    return await delete_item({
        'srl': srl,
    }, req=req)

# change nest
@router.patch('/{srl:int}/change-srl/')
async def _patch_change_srl(
    req: Request,
    srl: int,
    app_srl: int = Form(None, alias='app'),
    nest_srl: int = Form(None, alias='nest'),
):
    from .patch_change_srl import patch_change_srl
    return await patch_change_srl({
        'srl': srl,
        'app_srl': app_srl,
        'nest_srl': nest_srl,
    }, req=req)
