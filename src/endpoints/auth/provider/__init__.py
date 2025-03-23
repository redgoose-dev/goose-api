from . import discord
from . import github
from . import password

# provider code
class ProviderCode:
    discord = 'discord'
    github = 'github'
    google = 'google'
    password = 'password'
    @staticmethod
    def check_exist(val: str) -> bool:
        members = [
            ProviderCode.discord,
            ProviderCode.github,
            ProviderCode.google,
            ProviderCode.password,
        ]
        return True if val in members else False

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

def check_user_id(provider: str, user_id: str, user_data: dict) -> bool:
    match provider:
        case ProviderCode.discord:
            return discord.check_user_id(user_id, user_data)
        case _:
            return False
