import os, httpx
from src.libs.string import uri_decode, get_url

# set values
scope = 'identify email'
header_type = 'Bearer'
client_id = os.getenv('AUTH_DISCORD_CLIENT_ID')
client_secret = os.getenv('AUTH_DISCORD_CLIENT_SECRET')

# url
url_authorization = 'https://discord.com/oauth2/authorize'
url_token = 'https://discord.com/api/oauth2/token'
url_revoke = 'https://discord.com/api/oauth2/token/revoke'
url_userinfo = 'https://discord.com/api/users/@me'

async def get_token(code: str) -> dict|None:
    async with httpx.AsyncClient() as client:
        res = await client.post(
            url = url_token,
            data = {
                'client_id': client_id,
                'client_secret': client_secret,
                'grant_type': 'authorization_code',
                'code': code,
                'redirect_uri': get_url('/auth/callback/discord/'),
            },
        )
        if res.status_code != 200: raise Exception(res.text)
        json = res.json()
    return {
        'access_token': json['access_token'],
        'refresh_token': json['refresh_token'],
        'expires_in': json['expires_in'],
    }

async def get_user(access_token: str) -> dict|None:
    async with httpx.AsyncClient() as client:
        res = await client.get(
            url = url_userinfo,
            headers = { 'Authorization': f'{header_type} {access_token}' },
        )
        if res.status_code != 200: raise Exception(res.text)
        json = res.json()
    return {
        'id': json['id'],
        'name': json['username'],
        'avatar': json['avatar'],
        'email': json['email'],
    }
