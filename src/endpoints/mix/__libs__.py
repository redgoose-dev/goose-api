import re
from src.libs.object import check_keys_exist, get_value_dict

def get_router(path: str) -> callable:
    match path:
        case 'get /':
            from ..get_home import home
            return home
        case 'get /app':
            from ..app.get_index import get_index
            return get_index
        case 'get /app/{srl}':
            from ..app.get_item import get_item
            return get_item
        case 'put /app':
            from ..app.put_item import put_item
            return put_item
        case _:
            return None

def parse_requests(data: list) -> dict:
    result = {}
    for item in data:
        if not check_keys_exist(item, ['key','url']): continue
        method = item['method'].lower() if 'method' in item else 'get'
        route = get_router(f'{method} {item['url']}')
        if not route: continue
        result[item['key']] = {}
        result[item['key']]['func'] = route
        params = item['params'] if 'params' in item else {}
        result[item['key']]['params'] = {
            **params,
        }
    return result

def parse_params(params: dict, data: dict = {}) -> dict:
    keys = list(params.keys())
    for key in keys:
        if not (key in params): continue
        pattern = re.compile(r'^\{\{(.*)\}\}$')
        match = pattern.match(params[key])
        if match:
            value = get_value_dict(data, match.group(1))
            if isinstance(value, (str, int, bool)):
                params[key] = value
            elif value is None:
                del params[key]

    return params
