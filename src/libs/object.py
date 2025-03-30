import json

def json_stringify(data: dict|list, space: int|None = None) -> str:
    return json.dumps(
        data,
        indent = space,
        ensure_ascii = False
    )

def json_parse(text: str) -> dict|None:
    try: return json.loads(text)
    except json.JSONDecodeError as _: return None

def check_keys_exist(data: dict, keys: list) -> bool:
    return all(key in data for key in keys)

def get_value_dict(data: dict, path: str):
    keys = path.split('.')
    item = data
    for key in keys:
        if '[' in key and ']' in key:
            key, index = key[:-1].split('[')
            if key in item and isinstance(item[key], list) and int(index) < len(item[key]):
                item = item[key][int(index)]
            else:
                return None
        elif key in item:
            item = item[key]
        else:
            return None
    return item
