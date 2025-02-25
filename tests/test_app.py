from fastapi.testclient import TestClient
from main import app

client = TestClient(app)

def test_root():
    res = client.get('/app/')
    assert res.status_code == 200
    # assert res.json() == { 'message': 'Hello World' }
