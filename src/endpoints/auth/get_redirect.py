from . import __types__ as types
from urllib.parse import urlencode
from src import output
from src.libs.string import uri_encode, get_url
from . import provider

"""
# URL Example
GET /auth/redirect/discord/?redirect_uri={CLIENT_REDIRECT_URI}
"""

async def get_redirect(params: types.GetRedirect):

    # set values
    result = None

    try:
        # set state
        state = uri_encode({ 'redirect_uri': params.redirect_uri })

        # set provider
        provider_info = provider.get_info(params.provider)

        # set query string and url
        qs = urlencode({
            'client_id': provider_info['client_id'],
            'redirect_uri': get_url(f'/auth/callback/{params.provider}/'),
            'response_type': 'code',
            'scope': provider_info['scope'],
            'state': state,
        })
        url = f'{provider_info['url_authorization']}/?{qs}'

        # set result
        result = output.redirect(url)
        pass
    except Exception as e:
        result = output.exc(e)
    finally:
        return result
