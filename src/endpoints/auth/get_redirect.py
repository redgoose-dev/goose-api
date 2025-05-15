from src import output
from src.libs.db import DB, Table
from src.libs.string import uri_encode
from src.modules.verify import checking_token
from . import __types__ as types
from .provider import Provider

"""
OAuth 서비스로 리다이렉트한다.

# URL Example
GET /auth/redirect/discord/?redirect_uri={CLIENT_REDIRECT_URI}
"""

async def get_redirect(params: dict = {}, req = None, _db: DB = None, _check_token = True):

    # set values
    result = None
    db = _db if _db else DB().connect()

    try:
        # set params
        params = types.GetRedirect(**params)

        # set state
        state = uri_encode({
            'redirect_uri': params.redirect_uri,
            'access_token': params.access_token,
        })

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
        if not _db and db: db.disconnect()
        return result
