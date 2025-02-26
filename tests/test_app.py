from fastapi.testclient import TestClient
from main import app

client = TestClient(app)

def test_index():
    print('')
    res = client.put('/app/')
    print('ROOOOTTTT')
    assert res.status_code == 200
    # assert res.json() == { 'message': 'Hello World' }

# def test_item():
#     print('\n')
#     res = client.put('/app/1/')
#     assert res.status_code == 200
#     # assert res.json() == { 'message': 'Hello World' }

def test_add_item():
    print('')
    res = client.put('/app/')
    json = res.json()
    assert res.status_code == 200
