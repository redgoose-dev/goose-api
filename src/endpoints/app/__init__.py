from fastapi import APIRouter, Form, Query
from . import types
from .get_index import index
from .get_item import item
from .put_item import add_item

# set router
router = APIRouter()

# get apps index
@router.get('/')
async def _index():
    return await index()

# get app
@router.get('/{id:int}/')
async def _item(id: int):
    return await item(types.GetItem(
        id=id,
    ))

# add app
@router.put('/')
async def _add_item(
    id: str = Form(...),
    name: str = Form(...),
    description: str = Form(...),
):
    return await add_item(types.AddItem(
        id=id,
        name=name,
        description=description
    ))

# edit app
# @router.patch('/{id:int}/')

# delete app
# @router.delete('/{id:int}/')
