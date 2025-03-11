import pytest
from fastapi.testclient import TestClient
from main import app
from src.libs.string import create_random_string, date_format, get_date, date_shift

client = TestClient(app)

def pytest_addoption(parser):
    parser.addoption("--foo", action="store", default="default_value", help="foo parameter")

def get_index(params: dict = {}) -> list:
    res = client.get(
        url = '/article/',
        params = params,
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), dict)
    assert 'total' in json['data'] and isinstance(json['data']['total'], int)
    assert 'index' in json['data'] and isinstance(json['data']['index'], list)
    return json['data']['index']

def get_item(srl: int = None, params: dict = {}) -> dict:
    res = client.get(f'/article/{srl}/', params = params)
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    return json['data']

def put_item() -> int|None:
    res = client.put(f'/article/')
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), int)
    return json.get('data')

def patch_item(srl: int, data: dict = {}):
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

@pytest.mark.skip
def test_working():
    delete_item(1245)

# @pytest.mark.skip
def test_add_update_delete_item():
    # add item
    srl = put_item()
    # update item
    patch_item(srl, {
        # 'app': 1,
        # 'nest': 1,
        # 'category': 1,
        'title': 'TITLE',
        'content': 'EDITED CONTENT',
        'hit': True,
        'star': False,
        'json': '{"FOO":"BAR"}',
        'mode': 'public',
        'regdate': '2024-10-04',
    })
    # delete item
    delete_item(srl)

# @pytest.mark.skip
def test_get_items():
    index = get_index()
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['srl'])

@pytest.mark.skip
def test_add_items(request):
    count: int = int(request.config.getoption('--foo') or 10)
    date = get_date()
    for i in range(count):
        new_date = date_format(date_shift(date, tomorrow=False, day=i), '%Y-%m-%d')
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
            'regdate': new_date,
        })
