import sys, pytest

def pytest_collection_modifyitems(config, items):
    # 모든 테스트 항목에 접근 가능 (필요 시 추가 로직)
    pass

def pytest_addoption(parser):
    parser.addoption('--foo', action='store', default='', help='Anything use value')
