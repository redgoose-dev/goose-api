from fastapi.testclient import TestClient
from main import app

client = TestClient(app)

def test_home():
    res = client.get('/')
    assert res.status_code == 200
    assert res.json() == { 'message': 'Hello World' }

def test_preflight():
    res = client.options('/')
    assert res.status_code == 204
    assert res.headers['Access-Control-Allow-Origin'] == '*'
    assert res.headers['Access-Control-Allow-Methods'] == 'GET, POST, OPTIONS'
