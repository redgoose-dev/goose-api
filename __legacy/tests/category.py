import pytest
from fastapi.testclient import TestClient
from main import app
from . import default_headers
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
        url = '/category/',
        params = params,
        headers = { **default_headers },
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), dict)
    assert 'total' in json['data'] and isinstance(json['data']['total'], int)
    assert 'index' in json['data'] and isinstance(json['data']['index'], list)
    return json['data']['index']

def get_item(srl: int, params: dict = {}) -> dict:
    res = client.get(
        url = f'/category/{srl}/',
        params = params,
        headers = { **default_headers },
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    return json['data']

def put_item(data: dict = {}) -> int:
    res = client.put(
        url = '/category/',
        data = data,
        headers = { **default_headers },
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    assert isinstance(json.get('data'), int)
    return json['data']

def patch_item(srl: int, data: dict = {}):
    if not srl: raise Exception('srl not found.')
    res = client.patch(
        url = f'/category/{srl}/',
        data = data,
        headers = { **default_headers },
    )
    assert res.status_code == 200

def patch_change_order(data: dict = {}):
    res = client.patch(
        url = f'/category/change-order/',
        data = data,
        headers = { **default_headers },
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.')
    res = client.delete(
        url = f'/category/{srl}/',
        headers = { **default_headers },
    )
    assert res.status_code == 200

@pytest.mark.skip
def test_working():
    put_item({
        'name': create_random_string(4),
        'module': 'json',
        # 'module_srl': 2,
    })
    pass

# @pytest.mark.skip
def test_add_update_delete_item():
    module = 'nest'
    module_srl = 2
    srl = put_item({
        'name': create_random_string(16),
        'module': module,
        'module_srl': module_srl,
    })
    patch_item(srl, {
        'name': create_random_string(8),
        'module': module,
        'module_srl': module_srl,
    })
    delete_item(srl)

# @pytest.mark.skip
def test_get_items():
    index = get_index()
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['srl'])
