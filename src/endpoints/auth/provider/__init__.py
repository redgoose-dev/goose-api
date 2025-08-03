import os
from src import libs
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
    type = ''
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

    @staticmethod
    def get_providers():
        return [
            Provider.code_discord,
            Provider.code_github,
            Provider.code_google,
            Provider.code_password,
        ]

    @staticmethod
    def get_authorize_url(provider: str, callback_url: str = '') -> str:
        if not (provider and callback_url):
            raise Exception('Provider and callback_url are required.')
        url = libs.url_path
        return f'{url}/auth/redirect/{provider}/?redirect_uri={callback_url}'

    # PUBLIC METHODS

    def create_authorize_url(self, state: str): pass

    async def get_token(self, code: str) -> dict|None: pass

    async def get_user(self, token: str) -> dict|None: pass

    def check_user_id(self, user_id: str, user_data: dict) -> bool: pass

    async def renew_access_token(self, refresh_token: str) -> dict|None: pass

    def verify_password(self): pass

    def create_token(self): pass
