import os, httpx
from . import discord

# provider code
class ProviderCode:
    discord = 'discord'
    github = 'github'
    google = 'google'
    password = 'password'

def get_info(provider: str) -> dict|None:
    match provider:
        case ProviderCode.discord:
            return {
                'client_id': discord.client_id,
                'client_secret': discord.client_secret,
                'url_authorization': discord.url_authorization,
                'url_token': discord.url_token,
                'url_revoke': discord.url_revoke,
                'scope': discord.scope,
            }
        case _:
            return None

async def get_token(provider: str, code: str) -> dict|None:
    match provider:
        case ProviderCode.discord:
            return await discord.get_token(code)

async def get_user(provider: str, token: str) -> dict|None:
    match provider:
        case ProviderCode.discord:
            return await discord.get_user(token)
