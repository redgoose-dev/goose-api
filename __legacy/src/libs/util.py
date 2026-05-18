from fastapi import Request

# setup env
def setup_env():
    from dotenv import load_dotenv
    load_dotenv('.env')
    load_dotenv('.env.local', override=True)

def get_authorization(req: Request, allow_query: bool = False) -> str|None:
    if allow_query:
        query = dict(req.query_params)
        if query.get('_a'): return query['_a']
    return req.headers.get('authorization')

def jprint(data: dict|list|str):
    import json
    from .object import json_parse
    if isinstance(data, str):
        print(type(data))
        data = json_parse(data)
    data = json.dumps(data, indent=2, ensure_ascii=False)
    if data: print(data)

# 쿼리스트링을 딕셔너리로 변환한다.
def query_to_dict(query: str) -> dict|None:
    if not query: return None
    return { k: v for k, v in [item.split('=') for item in query.split('&')] }
