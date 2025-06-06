import pytest, time
from fastapi.testclient import TestClient
from main import app
from . import default_headers
from src.libs.string import create_random_string, date_format, get_date, date_shift
from src.endpoints.file.__libs__ import get_mime_type, get_file_name

client = TestClient(app)

def get_index(params: dict = {}) -> list:
    res = client.get(
        url = '/article/',
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
    res = client.get(
        url = f'/article/{srl}/',
        params = params,
        headers = { **default_headers },
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    return json['data']

def put_item() -> int:
    res = client.put(
        url = f'/article/',
        headers = { **default_headers },
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json and isinstance(json.get('data'), int)
    return json.get('data')

def patch_item(srl: int, data: dict = {}):
    if not srl: raise Exception('srl not found.')
    res = client.patch(
        url = f'/article/{srl}/',
        data = data,
        headers = { **default_headers },
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.')
    res = client.delete(
        url = f'/article/{srl}/',
        headers = { **default_headers },
    )
    assert res.status_code == 200

### TEST AREA ###

@pytest.mark.skip
def test_working():
    pass

@pytest.mark.skip
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

@pytest.mark.skip
def test_get_items():
    index = get_index()
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['srl'])

# @pytest.mark.skip
def test_add_items(request):
    try: count: int = int(request.config.getoption('--count'))
    except ValueError: count = 0
    count = count if count > 0 else 60
    tomorrow = True
    date = get_date()
    for i in range(count):
        new_date = date_format(date_shift(date, tomorrow=tomorrow, day=i), '%Y-%m-%d')
        srl = put_item()
        patch_item(srl, {
            # 'app': 1,
            # 'nest': 1,
            # 'category': 1,
            'title': create_random_string(10),
            'content': create_random_string(20),
            'hit': False,
            'star': False,
            'json': '{"apple":"red"}',
            'mode': 'public',
            'regdate': new_date,
        })

@pytest.mark.skip
def test_make_delete():
    # add article
    srl = put_item()
    patch_item(srl, {
        'app': 1,
        'nest': 2,
        'category': 1,
        'title': create_random_string(8),
        'content': create_random_string(16),
        'hit': True,
        'star': False,
        'json': '{"FOO":"BAR"}',
        'mode': 'public',
        'regdate': '2024-10-04',
        'tag': 'TAG1,TAG2,TAG3',
    })
    # file
    path = '/Users/goose/Pictures/scrap/character/h38c.jpg'
    res = client.put(
        url = f'/file/',
        data = {
            'module': 'article',
            'module_srl': srl,
            'json': '{ "FOO": "BAR" }',
        },
        files = {
            'file': (get_file_name(path), open(path, 'rb'), get_mime_type(path)),
        },
        headers = { **default_headers },
    )
    assert res.status_code == 200
    # comment
    res = client.put(
        url = '/comment/',
        data = {
            'content': create_random_string(24),
            'module': 'article',
            'module_srl': srl,
        },
        headers = { **default_headers },
    )
    assert res.status_code == 200
    # delay 5 seconds
    time.sleep(8)
    delete_item(srl)
