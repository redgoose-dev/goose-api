from . import types

async def item(params: types.GetItem):
    return {
        'message': 'app-get-item',
        'params': params.model_dump(),
    }
