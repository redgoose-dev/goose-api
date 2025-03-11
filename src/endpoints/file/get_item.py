from typing import Optional
from . import __types__ as types
from src import output
from src.libs.db import DB, Table
from src.libs.string import convert_date
from src.libs.object import json_parse
from .__lib__ import convert_path_to_buffer

async def get_item(params: types.GetItem, _db: DB = None):
    # set values
    result = None

    # connect db
    if _db: db = _db
    else: db = DB().connect()

    try:
        # set srl
        srl: Optional[int] = None
        code: Optional[str] = None
        try: srl = int(params.srl)
        except ValueError: code = str(params.srl)

        # set values
        # use_cache = True if code is not None else False
        # data = {
        #     'path': None,
        #     'mime': None,
        #     'buffer': None,
        # }

        # set where
        if code is not None:
            where = [ f'and code LIKE "{code}"' ]
        elif srl is not None:
            where = [ f'and srl={srl}' ]
        else:
            raise Exception('srl or code is required', 404)

        # TODO: VERSION #1 START ###########################
        # get item
        item = db.get_item(
            table_name = Table.FILE.value,
            where = where,
        )
        if not item:
            raise Exception('Item not found', 404)
        if not item.get('path', None):
            raise Exception('Not found path in item', 404)
        # convert path to buffer
        buffer = convert_path_to_buffer(item['path'])
        # set result
        result = output.buffer(buffer, {
            'headers': {
                'Content-Type': item['mime'],
                'Content-Length': str(item['size']),
            },
        })
        # TODO: VERSION #1 END #############################

        # TODO: 2차 개발(완성)
        # TODO: 코드가 있다면 캐시파일 사용
            # TODO: YES? 캐시파일 json 데이터 가져오기:
                # TODO: YES? 공개인지 확인하기:
                    # TODO: YES? 데이터 사용하기
                    # TODO: NO? 인증 검사하고 허용 안되면 예외처리
                # TODO: NO? 문제가 있는 파일이라고 인식하고 캐시파일을 삭제한다.
        # TODO: 캐시파일에서 가져온 데이터가 없다면: YES?
            # TODO: DB에서 데이터를 가져온다. 있다면:
                # TODO: NO? 데이터가 없으니 예외처리(404)
                # TODO: item.path 파일이 없다면? 예외처리 (404)
            # TODO: module, target_srl 값이 있다면: DB에서 부모 데이터 가져온다?
                # TODO: YES? 데이터의 상태를 알아본다:
                    # TODO: 비공개 데이터: YES?
                        # TODO: 인증 검사하고 허용 안되면 예외처리(403)
                    # TODO: 파일이 이미지 and 리사이즈?
                        # TODO: YES? 이미지 리사이즈하여 새로운 데이터를 만든다.
                        # TODO: NO? 패스 경로로 가져온다.
                    # TODO: use_cache: YES?
                        # TODO: 캐시 데이터를 만든다.
                # TODO: data.path, data.mime, data.buffer 값 세트
        # TODO: data.path and data.mime이 없다면: YES?
            # TODO: 파일 데이터가 없다고 예외처리(404)
        # TODO: data.buffer 값이 없다면 output.path 값으로 data.buffer 만든다.
        # TODO: 헤더는 `Content-Type`, `Content-Length` 설정하기
        # TODO: prod 모드라면 `Cache-Control` 설정하기
        # TODO: content 값은 `data.buffer`로 설정하기

    except Exception as e:
        result = output.exc(e)
    finally:
        if not _db: db.disconnect()
        return result
