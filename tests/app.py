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
    res = client.get('/app/', params = params)
    assert res.status_code == 200
    json = res.json()
    assert isinstance(json.get('data'), dict)
    assert 'index' in json['data']
    return json['data']['index']

def get_item(srl: int, params: dict = {}) -> dict:
    res = client.get(f'/app/{srl}/', params = params)
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    return json['data']

def put_item(data: dict = {}) -> int:
    res = client.put(
        url = '/app/',
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
        url = f'/app/{srl}/',
        data = data,
    )
    assert res.status_code == 200

def delete_item(srl: int):
    res = client.delete(f'/app/{srl}/')
    assert res.status_code == 200

### TEST AREA ###

@pytest.mark.skip
def test_working():
    index = get_index({
        # 'code': '',
        # 'name': '',
        'fields': 'srl',
        # 'page': 1,
        # 'size': 2,
        # 'order': 'srl',
        # 'sort': 'desc',
        'unlimited': False,
    })

# @pytest.mark.skip
def test_add_update_delete_item():
    srl = put_item({
        'code': create_random_string(4),
        'name': create_random_string(8),
        'description': create_random_string(16),
    })
    assert isinstance(srl, int)
    delete_item(srl)

# @pytest.mark.skip
def test_get_items():
    index = get_index()
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['srl'])

@pytest.mark.skip
def test_add_items(request):
    count: int = int(request.config.getoption('--foo') or 10)
    for i in range(count):
        put_item({
            'code': create_random_string(4),
            'name': create_random_string(8),
            'description': create_random_string(16),
        })
