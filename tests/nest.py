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
        url = '/nest/',
        params = params,
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), dict)
    assert 'total' in json['data'] and isinstance(json['data']['total'], int)
    assert 'index' in json['data'] and isinstance(json['data']['index'], list)
    return json['data']['index']

def get_item(srl: int = None, params: dict = {}) -> dict:
    if not srl: raise Exception('srl not found.')
    res = client.get(f'/nest/{srl}/', params = params)
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    return json['data']

def put_item(data: dict) -> int:
    res = client.put(
        url = f'/nest/',
        data = data,
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), int)
    return json.get('data')

def patch_item(srl: int, data: dict = {}):
    if not srl: raise Exception('srl not found.')
    res = client.patch(
        url = f'/nest/{srl}/',
        data = data,
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.')
    res = client.delete(f'/nest/{srl}/')
    assert res.status_code == 200

### TEST AREA ###

@pytest.mark.skip
def test_working():
    pass

# @pytest.mark.skip
def test_add_update_delete_item():
    srl = put_item({
        'app': 15,
        'code': create_random_string(10),
        'name': create_random_string(10),
        'description': create_random_string(10),
        'json': '{}',
    })
    patch_item(srl, {
        'app': 15,
        'code': create_random_string(10),
        'name': create_random_string(10),
        'description': create_random_string(10),
        'json': '{"FOO":"BAR"}',
    })
    # delete item
    delete_item(srl)

# @pytest.mark.skip
def test_get_items():
    index = get_index()
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['srl'])
