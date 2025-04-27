from fastapi import APIRouter, Request, Form, Query

# set router
router = APIRouter()

# get preference
@router.get('/')
async def _get_main(
    req: Request,
):
    from .get_main import get_main
    return await get_main(req=req)

# update preference
@router.patch('/')
async def _patch_main(
    req: Request,
    json_data: str = Form(..., alias='json'),
    change_data: bool = Form(False, convert=lambda v: bool(int(v)), alias='change'),
):
    from .patch_main import patch_main
    return await patch_main({
        'json_data': json_data,
        'change_data': change_data,
    }, req=req)
