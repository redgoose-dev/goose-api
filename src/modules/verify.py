from fastapi import Request
from datetime import datetime, timedelta
from src.libs.db import DB, Table
from src.libs.util import get_authorization


def checking_token(req: Request, db: DB, access_token: str = None, check_expires: bool = True, allow_query: bool = False) -> dict:

    """토큰을 검사한다.

    :param req: 리퀘스트 객체
    :param db: 데이터베이스 인스턴스 객체
    :param access_token: 사용자 엑세스 토큰
    :param check_expires: 만료시간 검사여부
    :param allow_query: url 쿼리스트링에서 토큰을 허용할지 여부. ex) `/?_a=your_token`
    :return: 엑세스 토큰 정보
    :rtype: dict
    """

    # set authorization and check exists
    authorization = access_token or get_authorization(req, allow_query)
    if not authorization: raise Exception('Authorization header not found.', 401)

    # get token
    token = db.get_item(
        table_name = Table.TOKEN.value,
        where = [ f'access LIKE \'{authorization}\'' ],
    )
    if not token: raise Exception('Token not found.', 401)
    if token.get('expires') <= 0: raise Exception('The token is unavailable.', 401)

    # 만료시간 검사하기
    if check_expires:
        expiration_time = datetime.strptime(token['created_at'], '%Y-%m-%d %H:%M:%S') + timedelta(seconds = token['expires'])
        if datetime.now() > expiration_time: raise Exception('Expired token.', 401)

    # return token
    return token
