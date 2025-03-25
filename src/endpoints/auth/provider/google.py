import os, httpx
from urllib.parse import urlencode
from src.libs.string import get_url

class ProviderGoogle:

    # set values
    name = 'google'
    scope = 'openid email profile'
    header_type = 'Bearer'
    client_id = os.getenv('AUTH_GOOGLE_CLIENT_ID')
    client_secret = os.getenv('AUTH_GOOGLE_CLIENT_SECRET')

    # url path
    url_authorization = 'https://accounts.google.com/o/oauth2/v2/auth'
    url_token = 'https://oauth2.googleapis.com/token'
    url_userinfo = 'https://www.googleapis.com/oauth2/v2/userinfo'

    def create_authorize_url(self, state: str):
        qs = urlencode({
            'client_id': self.client_id,
            'redirect_uri': get_url(f'/auth/callback/{self.name}/'),
            'response_type': 'code',
            'scope': self.scope,
            'prompt': 'consent',
            'access_type': 'offline',
            'state': state,
        })
        return f'{self.url_authorization}?{qs}'

    async def get_token(self, code: str) -> dict | None:
        async with httpx.AsyncClient() as client:
            res = await client.post(
                url = self.url_token,
                headers = { 'Accept': 'application/json' },
                data = {
                    'client_id': self.client_id,
                    'client_secret': self.client_secret,
                    'code': code,
                    'grant_type': 'authorization_code',
                    'redirect_uri': get_url(f'/auth/callback/{self.name}/'),
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
            'avatar': json['picture'],
        }

    def check_user_id(self, user_id: str, user_data: dict) -> bool:
        return user_id == user_data['id']

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
            'refresh': refresh_token,
        }
