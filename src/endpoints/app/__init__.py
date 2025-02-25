from fastapi import APIRouter
from .get_index import index
from .get_item import item

# set router
router = APIRouter()

# get apps index
@router.get('/')
async def _index():
    return await index()

# get app
@router.get('/{id:int}/')
async def _item():
    return await item()

# add app
# @router.put('/')

# edit app
# @router.patch('/{id:int}/')

# delete app
# @router.delete('/{id:int}/')
