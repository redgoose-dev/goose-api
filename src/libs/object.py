import json

def json_stringify(data: dict, space: int|None = None) -> str:
    return json.dumps(
        data,
        indent = space,
        ensure_ascii = False
    )

def json_parse(text: str) -> dict:
    return json.loads(text)

