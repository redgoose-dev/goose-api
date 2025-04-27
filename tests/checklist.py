import pytest, time
from fastapi.testclient import TestClient
from main import app
from . import default_headers
from src.libs.string import create_random_string, date_format, get_date, date_shift
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
        url = '/checklist/',
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
        url = f'/checklist/{srl}/',
        params = params,
        headers = { **default_headers },
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    return json['data']

def put_item(data: dict) -> int:
    res = client.put(
        url = f'/checklist/',
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
        url = f'/checklist/{srl}/',
        data = data,
        headers = { **default_headers },
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.')
    res = client.delete(
        url = f'/checklist/{srl}/',
        headers = { **default_headers },
    )
    assert res.status_code == 200

### TEST AREA ###

@pytest.mark.skip
def test_working():
    index = get_index()

# @pytest.mark.skip
def test_make_delete():
    # add checklist
    checklist_srl = put_item({
        'content': 'description\n- [x] item\n- [ ] item\n- [x] item\n- [x] item',
        'tag': f'{create_random_string(4)},{create_random_string(4)}'
    })
    # update checklist
    patch_item(checklist_srl, {
        'content': 'QQQQQQ\n- [ ] xx\n- [x] xxx\n- [ ] wwww\n- [ ] dddddddd',
    })
    # add file
    path = '/Users/goose/Pictures/scrap/character/h38c.jpg'
    res = client.put(
        url = f'/file/',
        data = {
            'module': 'checklist',
            'module_srl': checklist_srl,
            'json': '{ "FOO": "BAR" }',
        },
        files = {
            'file': (get_file_name(path), open(path, 'rb'), get_mime_type(path)),
        },
        headers = { **default_headers },
    )
    assert res.status_code == 200
    # delay
    time.sleep(8)
    # delete
    delete_item(checklist_srl)

@pytest.mark.skip
def test_get_items():
    index = get_index()
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['srl'])
