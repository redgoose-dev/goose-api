from fastapi import APIRouter, Request, Depends, Form, Query
from typing import List, Dict, Any
from src import output
from src.libs.db import DB
from src.libs.object import json_parse
from .__libs__ import parse_requests, parse_params

# set router
router = APIRouter()

@router.post('/')
async def _init(
    req: Request,
    data: List[Dict[str, Any]],
):

    # set values
    response = {}
    db = DB().connect()

    try:
        # parsing requests
        requests = parse_requests(data)

        # run requests
        keys = list(requests.keys())
        while keys:
            key = keys.pop(0)
            try:
                request = requests[key]
                if 'func' not in request: continue
                params = parse_params(request.get('params', None), response)
                # print('PARAMS => ', params)
                res = await request['func'](
                    params = params,
                    req = req,
                    _db = db,
                )
                data = json_parse(res.body.decode()) if bool(res.body) else {}
                response[key] = {
                    'status_code': res.status_code,
                    **data,
                }
            except Exception as e:
                raise Exception(f'[{key}] {e}', 400)
        response = output.success(response)
    except Exception as e:
        print('ERROR START)')
        print(e)
        print('ERROR END)')
        response = output.exc(e)
    finally:
        db.disconnect()
        return response
