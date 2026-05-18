import os, httpx
from urllib.parse import urlencode
from src import libs

class ProviderGithub:

    # set values
    name = 'github'
    type = 'OAuth'
    scope = 'user'
    header_type = 'Bearer'
    client_id = os.getenv('AUTH_GITHUB_CLIENT_ID')
    client_secret = os.getenv('AUTH_GITHUB_CLIENT_SECRET')

    # url path
    url_authorization = 'https://github.com/login/oauth/authorize'
    url_token = 'https://github.com/login/oauth/access_token'
    url_userinfo = 'https://api.github.com/user'

    def create_authorize_url(self, state: str):
        qs = urlencode({
            'client_id': self.client_id,
            'redirect_uri': f'{libs.url_path}/auth/callback/{self.name}/',
            'scope': self.scope,
            'state': state,
        })
        return f'{self.url_authorization}/?{qs}'

    async def get_token(self, code: str) -> dict | None:
        async with httpx.AsyncClient() as client:
            res = await client.post(
                url = self.url_token,
                headers = { 'Accept': 'application/json' },
                data = {
                    'client_id': self.client_id,
                    'client_secret': self.client_secret,
                    'code': code,
                    'redirect_uri': f'{libs.url_path}/auth/callback/{self.name}/',
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
            'name': json['name'],
            'email': json['email'],
            'avatar': json['avatar_url'],
        }

    def check_user_id(self, user_id: str, user_data: dict) -> bool:
        return str(user_id) == str(user_data['id'])

    async def renew_access_token(self, refresh_token: str) -> dict | None:
        if not refresh_token: return None
        async with httpx.AsyncClient() as client:
            res = await client.post(
                url = self.url_token,
                headers = { 'Accept': 'application/json' },
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
