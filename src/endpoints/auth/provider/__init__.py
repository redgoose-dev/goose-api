from . import discord
from . import github
from . import password

class Provider:

    # codes
    code_discord = 'discord'
    code_github = 'github'
    code_google = 'google'
    code_password = 'password'

    # set values
    name = ''
    scope = ''
    header_type = ''
    client_id = ''
    client_secret = ''

    # set urls
    url_authorization = ''
    url_token = ''
    url_userinfo = ''

    # initialize provider. 자식 클래스를 오버라이드한다.
    def __init__(self, provider: str):
        match provider:
            case self.code_discord:
                from .discord import ProviderDiscord
                self.__class__ = ProviderDiscord
            case self.code_github:
                from .github import ProviderGithub
                self.__class__ = ProviderGithub
            case self.code_google:
                from .google import ProviderGoogle
                self.__class__ = ProviderGoogle
            case self.code_password:
                from .password import ProviderPassword
                self.__class__ = ProviderPassword
            case _:
                raise Exception('Provider not found.')

    def create_authorize_url(self, state: str): pass

    async def get_token(self, code: str) -> dict|None: pass

    async def get_user(self, token: str) -> dict|None: pass

    def check_user_id(self, user_id: str, user_data: dict) -> bool: pass

    async def renew_access_token(self, refresh_token: str) -> dict|None: pass

    def verify_password(self): pass

    def create_token(self): pass
