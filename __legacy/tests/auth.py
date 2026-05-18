import pytest
from fastapi.testclient import TestClient
from main import app
from . import default_headers

client = TestClient(app)

def pytest_addoption(parser):
    parser.addoption(
        '--custom',
        action = 'store',
        default = 'default_value',
        help = 'custom parameter')
