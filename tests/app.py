from typing import Any, Dict
import pytest
from fastapi.testclient import TestClient
from main import app
from src.libs.string import create_random_string

client = TestClient(app)

def test_add_item():
    res = client.put(
        url = '/app/',
        data={
            'code': create_random_string(16),
            'name': 'NAMEEEE',
            'description': 'DESCRIPTIONNN',
        },
    )
    assert res.status_code == 200
    json = res.json()
    assert 'message' in json
    assert 'data' in json
    assert isinstance(json.get('data'), int)

# def test_index():
#     res = client.get('/app/')
#     assert res.status_code == 200
#     json = res.json()
#     assert isinstance(json.get('message'), str)
#     assert isinstance(json.get('data'), Dict)

# def test_item():
#     print('\n')
#     res = client.put('/app/1/')
#     assert res.status_code == 200
#     # assert res.json() == { 'message': 'Hello World' }

def test_delete_item():
    pass
