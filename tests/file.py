import pytest, time, sys
from typing import Any, Dict
from fastapi.testclient import TestClient
from main import app
from src.libs.string import create_random_string
from src.endpoints.file.__lib__ import get_mime_type, get_file_name

client = TestClient(app)

# TODO: async example
# TODO: `import asyncio`
# TODO: `item = await asyncio.gather(*[ get_item(srl=4) ])`

def get_index(params: Dict = {}) -> Dict:
    res = client.get(
        url = f'/file/',
        params = {
            # 'fields': 'srl',
            # 'module': 'article',
            # 'module_srl': 1,
            # 'name': 'h38c.jpg',
            # 'mime': 'image',
            # 'page': 1,
            # 'size': 10,
            # 'order': 'srl',
            # 'sort': 'desc',
            **params,
        },
    )
    assert res.status_code == 200
    assert 'data' in res.json()
    assert 'total' in res.json()['data']
    assert isinstance(res.json()['data']['total'], int)
    assert 'index' in res.json()['data']
    assert isinstance(res.json()['data']['index'], list)
    return res.json()['data']

def get_item(srl: int = 4) -> bytes|None:
    if not srl: raise Exception('srl not found.', 400)
    res = client.get(f'/file/{srl}/')
    assert res.status_code == 200
    assert isinstance(res.content, bytes)
    return res.content

def put_item(data: Dict = {}, files: Dict = {}) -> int:
    if not data or not files: raise Exception('Data not found.')
    res = client.put(
        url = f'/file/',
        data = data,
        files = files,
    )
    assert res.status_code == 200
    assert 'data' in res.json()
    assert isinstance(res.json()['data'], int)
    return res.json()['data']

def patch_item(srl: int, data: Dict = {}, files: Dict = {}):
    if not srl: raise Exception('srl not found.')
    if not data or not files: raise Exception('Data not found.')
    res = client.patch(
        url = f'/file/{srl}/',
        data = data,
        files = files,
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.', 400)
    res = client.delete(f'/file/{srl}/')
    assert res.status_code == 200


def test_basic():
    # TODO: 아티클 목록을 가져와서 한개를 가져와서 module_srl 값에 넣는다.
    # 파일 추가하기
    path = '/Users/goose/Pictures/scrap/character/h38c.jpg'
    srl = put_item(
        data = {
            'module': 'article',
            'module_srl': 1,
            'json': '{ "FOO": "BAR" }',
        },
        files = {
            'file': (get_file_name(path), open(path, 'rb'), get_mime_type(path)),
        },
    )
    # 파일 목록 가져오기
    get_index()
    # 파일 하나 가져오기
    get_item(srl)
    # 파일 교체하기
    re_path = '/Users/goose/Pictures/scrap/character/1211730_orig.jpg'
    patch_item(
        srl = srl,
        data = {
            'module': 'article',
            'module_srl': 1,
            'json': '{ "123": "4567" }',
        },
        files = {
            'file': (get_file_name(re_path), open(re_path, 'rb'), get_mime_type(re_path)),
        },
    )
    # 파일 삭제하기
    delete_item(srl)
