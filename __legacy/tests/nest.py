import pytest, time
from fastapi.testclient import TestClient
from main import app
from . import default_headers
from src.libs.string import create_random_string
from src.endpoints.file.__libs__ import get_mime_type, get_file_name

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
        headers = { **default_headers },
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), dict)
    assert 'total' in json['data'] and isinstance(json['data']['total'], int)
    assert 'index' in json['data'] and isinstance(json['data']['index'], list)
    return json['data']['index']

def get_item(srl: int = None, params: dict = {}) -> dict:
    if not srl: raise Exception('srl not found.')
    res = client.get(
        url = f'/nest/{srl}/',
        params = params,
        headers = { **default_headers },
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    return json['data']

def put_item(data: dict) -> int:
    res = client.put(
        url = f'/nest/',
        data = data,
        headers = { **default_headers },
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
        headers = { **default_headers },
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.')
    res = client.delete(
        url = f'/nest/{srl}/',
        headers = { **default_headers },
    )
    assert res.status_code == 200

### TEST AREA ###

@pytest.mark.skip
def test_working():
    pass

# @pytest.mark.skip
def test_add_update_delete_item():
    # add nest
    nest_srl = put_item({
        'app': 1,
        'code': create_random_string(10),
        'name': create_random_string(10),
        'description': create_random_string(10),
        'json': '{}',
    })
    # update nest
    patch_item(nest_srl, {
        'code': create_random_string(10),
        'name': create_random_string(10),
        'description': create_random_string(10),
        'json': '{"FOO":"BAR"}',
    })
    for i in range(3):
        # add article
        res = client.put(
            url = f'/article/',
            headers = { **default_headers },
        )
        assert res.status_code == 200
        article_srl = res.json().get('data')
        res = client.patch(
            url = f'/article/{article_srl}/',
            data = {
                'nest': str(nest_srl),
                'tag': f'{create_random_string(4)},{create_random_string(4)},{create_random_string(4)}',
            },
            headers = { **default_headers },
        )
        assert res.status_code == 200
        # add category
        res = client.put(
            url = '/category/',
            data = {
                'name': create_random_string(16),
                'module': 'nest',
                'module_srl': nest_srl,
            },
            headers = { **default_headers },
        )
        assert res.status_code == 200
        # add file
        file_path = '/Users/goose/Pictures/scrap/character/h38c.jpg'
        res = client.put(
            url = f'/file/',
            data = {
                'module': 'article',
                'module_srl': article_srl,
                'json': '{ "FOO": "BAR" }',
            },
            files = {
                'file': (get_file_name(file_path), open(file_path, 'rb'), get_mime_type(file_path)),
            },
            headers = { **default_headers },
        )
        assert res.status_code == 200
        # add comment
        res = client.put(
            url = '/comment/',
            data = {
                'content': create_random_string(24),
                'module': 'article',
                'module_srl': article_srl,
            },
            headers = { **default_headers },
        )
        assert res.status_code == 200
    time.sleep(10)
    # delete item
    delete_item(nest_srl)

@pytest.mark.skip
def test_get_items():
    index = get_index()
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['srl'])
