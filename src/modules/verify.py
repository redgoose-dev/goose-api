from fastapi import Request
from datetime import datetime, timedelta
from src.libs.db import DB, Table
from src.libs.util import get_authorization

def checking_token(req: Request, db: DB) -> dict:

    # set values
    authorization = get_authorization(req)

    # get token
    token = db.get_item(
        table_name = Table.TOKEN.value,
        where = [ f'access LIKE \'{authorization}\'' ],
    )
    if not token: raise Exception('Token not found.', 401)

    # 만료시간 검사하기
    expiration_time = datetime.strptime(token['created_at'], '%Y-%m-%d %H:%M:%S') + timedelta(seconds = token['expires'])
    if datetime.now() > expiration_time: raise Exception('Expired token.', 401)

    # return token
    return token
