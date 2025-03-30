import pytest
from fastapi.testclient import TestClient
from main import app
from src.libs.string import create_random_string

client = TestClient(app)

def pytest_addoption(parser):
    parser.addoption(
        '--custom',
        action = 'store',
        default = 'default_value',
        help = 'custom parameter',
    )

def get_index(params: dict = {}) -> list:
    res = client.get(
        url = '/comment/',
        params = params,
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), dict)
    assert 'total' in json['data'] and isinstance(json['data']['total'], int)
    assert 'index' in json['data'] and isinstance(json['data']['index'], list)
    return json['data']['index']

def get_item(srl: int = None, params: dict = {}) -> dict:
    res = client.get(f'/comment/{srl}/', params = params)
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    return json['data']

def put_item(data: dict = {}) -> int:
    res = client.put(
        url = '/comment/',
        data = data,
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    assert isinstance(json.get('data'), int)
    return json['data']

def patch_item(srl: int, data: dict = {}):
    if not srl: raise Exception('srl not found.')
    res = client.patch(
        url = f'/comment/{srl}/',
        data = data,
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.')
    res = client.delete(f'/comment/{srl}/')
    assert res.status_code == 200

### TEST AREA ###

@pytest.mark.skip
def test_working():
    pass

# @pytest.mark.skip
def test_add_update_delete_item():
    srl = put_item({
        'content': create_random_string(24),
        'module': 'article',
        'module_srl': 1234,
    })
    patch_item(srl, {
        'content': create_random_string(24),
    })
    delete_item(srl)

# @pytest.mark.skip
def test_get_items():
    index = get_index({
        # 'module': 'article',
        # 'module_srl': 1234,
        # 'content': 'P6oElkAXP',
        'fields': 'srl',
    })
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['srl'])

@pytest.mark.skip
def test_add_items(request):
    count: int = int(request.config.getoption('--custom') or 10)
