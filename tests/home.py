from fastapi.testclient import TestClient
from main import app
from . import default_headers

client = TestClient(app)

def test_home():
    res = client.get(
        url = '/',
        headers = { **default_headers },
    )
    assert res.status_code == 200
    # assert res.json() == { 'message': 'Hello World' }

def test_preflight():
    res = client.options('/')
    assert res.status_code == 204
    assert res.headers['Access-Control-Allow-Origin'] == '*'
    assert res.headers['Access-Control-Allow-Methods'] == 'GET, POST, OPTIONS'
