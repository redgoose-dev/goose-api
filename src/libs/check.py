import json

# TODO: 안쓸거 같은데..
def parse_json(json_text: str) -> dict:
    try:
        return json.loads(json_text)
    except json.JSONDecodeError as _:
        raise Exception('JSON parsing error.')

def check_url(url: str) -> bool:
    if url.startswith('http://') or url.startswith('https://'):
        return True
    else:
        raise Exception('URL validation error.')
