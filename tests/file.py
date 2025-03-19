import pytest
from fastapi.testclient import TestClient
from main import app
from src.endpoints.file.__lib__ import get_mime_type, get_file_name

client = TestClient(app)

def get_index(params: dict = {}) -> dict:
    res = client.get(
        url = f'/file/',
        params = params,
    )
    assert res.status_code == 200
    assert 'data' in res.json()
    assert 'total' in res.json()['data']
    assert isinstance(res.json()['data']['total'], int)
    assert 'index' in res.json()['data']
    assert isinstance(res.json()['data']['index'], list)
    return res.json()['data']['index']

def get_item(srl: int = 4, params: dict = {}) -> bytes:
    if not srl: raise Exception('srl not found.', 400)
    res = client.get(f'/file/{srl}/')
    assert res.status_code == 200
    assert isinstance(res.content, bytes)
    return res.content

def put_item(data: dict = {}, files: dict = {}) -> int:
    if not data or not files: raise Exception('Data not found.')
    res = client.put(
        url = f'/file/',
        data = data,
        files = files,
    )
    assert res.status_code == 200
    json = res.json()
    assert 'data' in json
    assert isinstance(json['data'], int)
    return json['data']

def patch_item(srl: int, data: dict = {}, files: dict = {}):
    if not srl: raise Exception('srl not found.')
    res = client.patch(
        url = f'/file/{srl}/',
        data = data,
        files = files,
    )
    assert res.status_code == 200

def delete_item(srl: int):
    if not srl: raise Exception('srl not found.', 400)
    res = client.delete(f'/file/{srl}/')
    assert res.status_code == 200

### TEST AREA ###

@pytest.mark.skip
def test_working():
    pass

# @pytest.mark.skip
def test_add_update_delete_item():
    # set values
    path = '/Users/goose/Pictures/scrap/character/h38c.jpg'
    article_srl = 1222
    # add item
    srl = put_item(
        data = {
            'module': 'article',
            'module_srl': article_srl,
            'json': '{ "FOO": "BAR" }',
        },
        files = { 'file': (get_file_name(path), open(path, 'rb'), get_mime_type(path)) },
    )
    # update item
    re_path = '/Users/goose/Pictures/scrap/character/1211730_orig.jpg'
    patch_item(
        srl = srl,
        data = { 'json': '{"123":"456"}' },
        files = { 'file': (get_file_name(re_path), open(re_path, 'rb'), get_mime_type(re_path)) },
    )
    # delete item
    delete_item(srl)

# @pytest.mark.skip
def test_get_items():
    index = get_index({
        'fields': 'srl,code',
        # 'module': 'article',
        # 'module_srl': 1222,
        # 'name': 'h38c.jpg',
        # 'mime': 'image',
        # 'page': 1,
        # 'size': 3,
        # 'order': 'srl',
        # 'sort': 'desc',
    })
    assert isinstance(index, list) and len(index) > 0
    get_item(index[0]['code'])
    get_item(index[0]['srl'])
