import pytest
from fastapi.testclient import TestClient
from main import app
from src.libs.util import jprint
from . import default_headers

client = TestClient(app)

def pytest_addoption(parser):
    parser.addoption(
        '--custom',
        action = 'store',
        default = 'default_value',
        help = 'custom parameter',
    )

### TEST AREA ###

# @pytest.mark.skip
def test_app():
    data = [
        {
            'key': 'get-app-index',
            'url': "/app/",
            'params': { 'size': 3 },
        },
        {
            'key': 'get-app-item',
            'url': '/app/{srl}/',
            'params': {
                'srl': '{{get-app-index.data.index[1].srl}}'
            },
        },
    ]
    keys = [item['key'] for item in data]
    res = client.post(
        url = '/mix/',
        json = data,
        headers = { **default_headers },
    )
    assert res.status_code == 200
    json = res.json()
    assert all(key in json for key in keys)
    # jprint(json)

# @pytest.mark.skip
def test_article():
    data = [
        {
            'key': 'get-article-index',
            'url': '/article/',
            'params': { 'size': 3 },
        },
        {
            'key': 'get-article-item',
            'url': '/article/{srl}/',
            'params': {
                'srl': '{{get-article-index.data.index[0].srl}}',
            },
        },
    ]
    keys = [item['key'] for item in data]
    res = client.post(
        url = '/mix/',
        headers = { **default_headers },
        json = data,
    )
    assert res.status_code == 200
    json = res.json()
    assert all(key in json for key in keys)
    # jprint(json)

# @pytest.mark.skip
def test_category():
    data = [
        {
            'key': 'get-category-index',
            'url': '/category/',
            'params': { 'size': 3 },
        },
        {
            'key': 'get-category-item',
            'url': '/category/{srl}/',
            'params': {
                'srl': '{{get-category-index.data.index[0].srl}}',
            },
        },
    ]
    keys = [item['key'] for item in data]
    res = client.post(
        url = '/mix/',
        headers = { **default_headers },
        json = data,
    )
    assert res.status_code == 200
    json = res.json()
    assert all(key in json for key in keys)
    # jprint(json)

# @pytest.mark.skip
def test_checklist():
    data = [
        {
            'key': 'get-checklist-index',
            'url': '/checklist/',
            'params': { 'size': 3 },
        },
        {
            'key': 'get-checklist-item',
            'url': '/checklist/{srl}/',
            'params': {
                'srl': '{{get-checklist-index.data.index[0].srl}}',
            },
        },
    ]
    keys = [item['key'] for item in data]
    res = client.post(
        url = '/mix/',
        headers = { **default_headers },
        json = data,
    )
    assert res.status_code == 200
    json = res.json()
    assert all(key in json for key in keys)
    # jprint(json)

# @pytest.mark.skip
def test_comment():
    data = [
        {
            'key': 'get-comment-index',
            'url': '/comment/',
            'params': { 'size': 3 },
        },
        {
            'key': 'get-comment-item',
            'url': '/comment/{srl}/',
            'params': {
                'srl': '{{get-comment-index.data.index[0].srl}}',
            },
        },
    ]
    keys = [item['key'] for item in data]
    res = client.post(
        url = '/mix/',
        headers = { **default_headers },
        json = data,
    )
    assert res.status_code == 200
    json = res.json()
    assert all(key in json for key in keys)
    # jprint(json)

# @pytest.mark.skip
def test_file():
    data = [
        {
            'key': 'get-file-index',
            'url': '/file/',
            'params': { 'size': 3 },
        },
    ]
    keys = [item['key'] for item in data]
    res = client.post(
        url = '/mix/',
        headers = { **default_headers },
        json = data,
    )
    assert res.status_code == 200
    json = res.json()
    assert all(key in json for key in keys)
    # jprint(json)

# @pytest.mark.skip
def test_json():
    data = [
        {
            'key': 'get-json-index',
            'url': '/json/',
            'params': { 'size': 3 },
        },
        {
            'key': 'get-json-item',
            'url': '/json/{srl}/',
            'params': {
                'srl': '{{get-json-index.data.index[0].srl}}',
            },
        },
    ]
    keys = [item['key'] for item in data]
    res = client.post(
        url = '/mix/',
        headers = { **default_headers },
        json = data,
    )
    assert res.status_code == 200
    json = res.json()
    assert all(key in json for key in keys)
    # jprint(json)

# @pytest.mark.skip
def test_nest():
    data = [
        {
            'key': 'get-nest-index',
            'url': '/nest/',
            'params': { 'size': 3 },
        },
        {
            'key': 'get-nest-item',
            'url': '/nest/{srl}/',
            'params': {
                'srl': '{{get-nest-index.data.index[0].srl}}',
            },
        },
    ]
    keys = [item['key'] for item in data]
    res = client.post(
        url = '/mix/',
        headers = { **default_headers },
        json = data,
    )
    assert res.status_code == 200
    json = res.json()
    assert all(key in json for key in keys)
    # jprint(json)
