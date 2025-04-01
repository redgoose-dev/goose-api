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

# 배열 두개를 비교하여 추가, 중복, 삭제 상황의 값들을 가져올 수 있다.
def compare_list(a: list, b: list) -> dict:
    def _filter(s):
        return s.strip() if isinstance(s, str) else s
    return {
        'added': [x for x in b if x and _filter(x) not in a],
        'duplicate': [x for x in b if x and _filter(x) in a],
        'removed': [x for x in a if x and _filter(x) not in b],
    }
