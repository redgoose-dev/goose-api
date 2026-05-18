from src import output
from src.libs.db import DB
from src.libs.object import json_parse
from src.modules.verify import checking_token
from .__libs__ import parse_requests, parse_params, check_if

async def post_main(params: list = [], req = None, _token = None):

    # set values
    response = {}
    db = DB().connect()

    try:
        # set token
        token = checking_token(req, db, use_public=True) if not _token else None

        # parsing requests
        requests = parse_requests(params)

        # run requests
        keys = list(requests.keys())
        while keys:
            key = keys.pop(0)
            try:
                request = requests[key]
                # set func
                if not (request and 'func' in request):
                    response[key] = None
                    continue
                # check 'if' condition
                if check_if(request.get('if'), response): continue
                # set params
                params = parse_params(request.get('params', None), response)
                # run function
                res = await request['func'](
                    params = params,
                    req = req,
                    _db = db,
                    _token = token,
                )
                # set response
                if res.status_code == 200:
                    if 'content-type' in res.headers and res.headers['content-type'] == 'application/json':
                        decoded = res.body.decode()
                        response[key] = json_parse(decoded) or {'message': decoded}
                    else:
                        response[key] = { 'message': 'Skipped because it is not of JSON type.' }
                else:
                    response[key] = { 'message': res.body.decode() }
                response[key]['status_code'] = res.status_code
                if 'error-code' in res.headers:
                    response[key]['error-code'] = res.headers.get('error-code')
            except Exception as e:
                raise Exception(f'[{key}] {e}', 400)
        keys = ','.join(response.keys())
        response['message'] = f'Complete "{keys}" requests.'
        response = output.success(response, _req=req)
    except Exception as e:
        response = output.exc(e, _req=req)
    finally:
        db.disconnect()
        return response
