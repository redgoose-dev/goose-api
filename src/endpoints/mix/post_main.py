from src import output
from src.libs.db import DB
from src.libs.object import json_parse
from src.modules.verify import checking_token
from .__libs__ import parse_requests, parse_params

async def post_main(params: list = [], req = None, _check_token = True):

    # set values
    response = {}
    db = DB().connect()

    try:
        # checking token
        if _check_token: checking_token(req, db)

        # parsing requests
        requests = parse_requests(params)

        # run requests
        keys = list(requests.keys())
        while keys:
            key = keys.pop(0)
            try:
                request = requests[key]
                if 'func' not in request: continue
                params = parse_params(request.get('params', None), response)
                res = await request['func'](
                    params = params,
                    req = req,
                    _db = db,
                    _check_token = False,
                )
                if res.status_code == 200:
                    if 'content-type' in res.headers and res.headers['content-type'] == 'application/json':
                        decoded = res.body.decode()
                        response[key] = json_parse(decoded) or {'message': decoded}
                    else:
                        response[key] = {
                            'message': 'Skipped because it is not of JSON type.',
                        }
                else:
                    response[key] = { 'message': res.body.decode() }
                response[key]['status_code'] = res.status_code
                if 'error_code' in res.headers:
                    response[key]['error_code'] = res.headers['error_code']
            except Exception as e:
                raise Exception(f'[{key}] {e}', 400)
        response = output.success(response, _req=req)
    except Exception as e:
        response = output.exc(e, _req=req)
    finally:
        db.disconnect()
        return response
