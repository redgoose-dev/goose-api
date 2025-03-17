import pytest
from fastapi.testclient import TestClient
from main import app
from src.libs.string import create_random_string

client = TestClient(app)

def get_index(params: dict = {}) -> list:
    res = client.get(
        url = '/json/',
        params = params,
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), dict)
    assert 'total' in json['data'] and isinstance(json['data']['total'], int)
    assert 'index' in json['data'] and isinstance(json['data']['index'], list)
    return json['data']['index']

def get_item(srl: int, params: dict = {}) -> dict:
    if not srl: raise Exception('srl not found.')
    res = client.get(f'/json/{srl}/', params = params)
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    return json['data']

def put_item(data: dict = {}) -> int:
    res = client.put(
        url = '/json/',
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
        url = f'/json/{srl}/',
        data = data,
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.')
    res = client.delete(f'/json/{srl}/')
    assert res.status_code == 200

### TEST AREA ###

@pytest.mark.skip
def test_working():
    item = get_item(5)
    print(item)

# @pytest.mark.skip
def test_add_update_delete_item():
    srl = put_item({
        'category': 11,
        'name': create_random_string(8),
        'description': create_random_string(16),
        'json': f'{{"foo":"{create_random_string(4)}"}}',
        'path': f'https://{create_random_string(8)}',
    })
    patch_item(srl, {
        'name': create_random_string(8),
        'description': create_random_string(16),
        'json': f'{{"foo":"{create_random_string(4)}"}}',
        'path': f'https://{create_random_string(8)}',
    })
    delete_item(srl)

# @pytest.mark.skip
def test_get_items():
    index = get_index()
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['srl'])
