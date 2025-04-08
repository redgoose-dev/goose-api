import os, httpx
from urllib.parse import urlencode
from src import libs
from src.libs.string import get_url

class ProviderDiscord:

    # set values
    name = 'discord'
    scope = 'identify email'
    header_type = 'Bearer'
    client_id = os.getenv('AUTH_DISCORD_CLIENT_ID')
    client_secret = os.getenv('AUTH_DISCORD_CLIENT_SECRET')

    # url path
    url_authorization = 'https://discord.com/oauth2/authorize'
    url_token = 'https://discord.com/api/oauth2/token'
    url_userinfo = 'https://discord.com/api/users/@me'

    @staticmethod
    def __get_avatar_url__(_id: str = None, _code: str = None) -> str:
        url = {
            'base': 'https://cdn.discordapp.com/avatars',
            'embed': 'https://cdn.discordapp.com/embed/avatars',
        }
        if _code:
            filename = f'{_code}.{'gif' if _code.startswith('a_') else 'png'}'
            return f'{url['base']}/{_id}/{filename}'
        else:
            return f'{url['embed']}/{int(_id) % 5}.png'

    def create_authorize_url(self, state: str):
        qs = urlencode({
            'client_id': self.client_id,
            'redirect_uri': f'{libs.url_path}/auth/callback/{self.name}',
            'response_type': 'code',
            'scope': self.scope,
            'state': state,
        })
        return f'{self.url_authorization}/?{qs}'

    async def get_token(self, code: str) -> dict|None:
        async with httpx.AsyncClient() as client:
            res = await client.post(
                url = self.url_token,
                data = {
                    'client_id': self.client_id,
                    'client_secret': self.client_secret,
                    'grant_type': 'authorization_code',
                    'code': code,
                    'redirect_uri': f'{libs.url_path}/auth/callback/{self.name}',
                },
            )
            if res.status_code != 200: raise Exception(res.text)
            json = res.json()
        return {
            'access': json['access_token'],
            'expires': json['expires_in'],
            'refresh': json['refresh_token'],
        }

    async def get_user(self, token: str) -> dict | None:
        async with httpx.AsyncClient() as client:
            res = await client.get(
                url = self.url_userinfo,
                headers = { 'Authorization': f'{self.header_type} {token}' },
            )
            if res.status_code != 200: raise Exception(res.text)
            json = res.json()
        return {
            'id': json['id'],
            'name': json['username'],
            'email': json['email'],
            'avatar': self.__get_avatar_url__(json['id'], json['avatar']),
        }

    def check_user_id(self, user_id: str, user_data: dict) -> bool:
        return user_id == user_data['id']

    async def renew_access_token(self, refresh_token: str) -> dict|None:
        if not refresh_token: return None
        async with httpx.AsyncClient() as client:
            res = await client.post(
                url = self.url_token,
                data = {
                    'client_id': self.client_id,
                    'client_secret': self.client_secret,
                    'grant_type': 'refresh_token',
                    'refresh_token': refresh_token,
                },
            )
            if res.status_code != 200: raise Exception(res.text, res.status_code or 400)
            json = res.json()
        return {
            'access': json['access_token'],
            'expires': json['expires_in'],
            'refresh': json['refresh_token'],
        }
