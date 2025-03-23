from . import __types__ as types
from src import output
from src.libs.db import DB, Table

# TODO: 엑세스 토큰을 받아서 유효성을 검사하고 계정 정보를 가져온다.
# TODO: `expired_in`값은 엑세스토큰에 대한 만료시간이다.
# TODO: 엑세스토큰이 없거나 만료되었다면 리프레시 토큰으로 재발급을 시도한다.
# TODO: 토큰이 만료되었는지 검사하기. `(created_at + expires) > now` 이런 조건아라면 만료되었다고 볼 수 있다.

# TODO: 인증과 엑세스 토큰 검사하고 사용자 정보 가져오기
# TODO: 패스워드 인증으로도 사용할 수 있을것이다. (data를 다른걸로 바꿔야함)
# TODO: 상황에 따라 다양하게 처리할것이다.
# TODO: result - 계정정보
# TODO: result - 상태 (로그인,로그아웃)

async def post_checking(params: types.PostChecking, req = None, db: DB = None):

    # set values
    result = None
    db = db if db and isinstance(db, DB) else DB().connect()

    print('PARAMS: ', params)

    try:
        result = output.success({
            'message': 'checking',
        })
    except Exception as e:
        result = output.exc(e)
    finally:
        if db: db.disconnect()
        return result
