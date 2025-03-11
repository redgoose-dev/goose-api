from typing import Any, Dict
import pytest
from fastapi.testclient import TestClient
from main import app
from src.libs.string import create_random_string

client = TestClient(app)

def get_index(params: Dict = {}) -> Dict:
    res = client.get(
        url = '/article/',
        params = params,
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), dict)
    assert 'total' in json['data'] and isinstance(json['data']['total'], int)
    assert 'index' in json['data'] and isinstance(json['data']['index'], list)
    return json['data']

def put_item() -> int|None:
    res = client.put(f'/article/')
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), int)
    return json.get('data')

def patch_item(srl: int, data: Dict = {}):
    if not srl: raise Exception('srl not found.')
    res = client.patch(
        url = f'/article/{srl}/',
        data = data,
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.')
    res = client.delete(f'/article/{srl}/')
    assert res.status_code == 200

def test_basic():
    # # add item
    # srl = put_item()
    # # update item
    # patch_item(srl, {
    #     # 'app': 1,
    #     # 'nest': 1,
    #     # 'category': 1,
    #     'title': 'TITLE',
    #     'content': 'CONTENT',
    #     'hit': True,
    #     'star': False,
    #     'json': '{"FOO":"BAR"}',
    #     'mode': 'public',
    #     'regdate': '2024-10-04',
    # })
    # get index
    index = get_index({
        'fields': 'srl',
        # 'q': 'TITLE',
        # 'mode': 'public',
        # 'duration': 'new,created_at,day,now',
        # 'random': 22252315,
        # 'order': 'srl',
        # 'sort': 'asc',
        'unlimited': False,
    })
    # get item
    # TODO: 이 부분부터 작업해야한다.
    # delete item

def test_many_make_items():
    # add item
    for i in range(100):
        srl = put_item()
        patch_item(srl, {
            # 'app': 1,
            # 'nest': 1,
            # 'category': 1,
            'title': create_random_string(10),
            'content': create_random_string(20),
            'hit': True,
            'star': True,
            'json': '{"FOO":"BAR"}',
            'mode': 'public',
            'regdate': '2024-10-04',
        })