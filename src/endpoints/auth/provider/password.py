import os, httpx
from src.libs.string import uri_decode, get_url

# set values

async def get_token(code: str) -> dict|None:
    pass

async def get_user(access_token: str) -> dict|None:
    pass

def check_user_id(user_id: str, user_data: dict) -> bool:
    pass

def get_avatar_url() -> str:
    pass
