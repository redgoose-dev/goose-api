from fastapi import Request

# setup env
def setup_env():
    from dotenv import load_dotenv
    load_dotenv('.env')
    load_dotenv('.env.local', override=True)

def get_authorization(req: Request):
    return req.headers.get('authorization')

def jprint(data: dict|list|str):
    import json
    from .object import json_parse
    if isinstance(data, str):
        print(type(data))
        data = json_parse(data)
    data = json.dumps(data, indent=2, ensure_ascii=False)
    if data: print(data)
