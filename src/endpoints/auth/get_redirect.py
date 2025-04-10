from . import __types__ as types
from src import output
from src.libs.string import uri_encode, get_url
from .provider import Provider

"""
# URL Example
GET /auth/redirect/discord/?redirect_uri={CLIENT_REDIRECT_URI}
"""

async def get_redirect(params: dict = {}):

    # set values
    result = None

    try:
        # set params
        params = types.GetRedirect(**params)

        # set state
        state = uri_encode({ 'redirect_uri': params.redirect_uri })

        # set provider instance
        _provider_ = Provider(params.provider)

        # set url
        url = _provider_.create_authorize_url(state = state)

        # set result
        result = output.redirect(url, _req=req)
        pass
    except Exception as e:
        result = output.exc(e, _req=req)
    finally:
        return result
