import pytest
from fastapi.testclient import TestClient
from main import app

client = TestClient(app)

def pytest_addoption(parser):
    parser.addoption(
        '--custom',
        action = 'store',
        default = 'default_value',
        help = 'custom parameter',
    )

### TEST AREA ###

def test_basic():
    print('test_basic()')
    client.post('/multi/')
    pass
