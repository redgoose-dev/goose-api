import os
from fastapi import Request

# setup env
def setup_env():
    from dotenv import load_dotenv
    load_dotenv('.env')
    load_dotenv('.env.local', override=True)

def get_authorization(req: Request):
    return req.headers.get('authorization')
