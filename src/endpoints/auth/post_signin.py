from PIL import Image
from . import __types__ as types
from src import output
from src.libs.db import DB, Table

async def post_signin(params: types.PostSignin, _db: DB = None):
    print('PARAMS:', params)
    pass

# TODO: id,password 값으로 인증하고 엑세스토큰, 리프레시 토큰, 만료시간을 응답받기
# TODO: 토큰 데이터 가져오기
# TODO: 토큰이 만료되었는지 검사하기. `(created_at + expires) > now` 이런 조건아라면 만료되었다고 볼 수 있다.
