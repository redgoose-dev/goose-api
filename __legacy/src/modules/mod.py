"""
무슨 기능을 사용할지 가려주는 역할을 하는 모듈
"""

class MOD:

    def __init__(self, raw: str):
        self.name = 'MOD'
        self.body = raw.split(',') if raw else []

    @property
    def index(self) -> list:
        return self.body

    @property
    def exist(self):
        return self.body and len(self.body) > 0

    def check(self, keyword: str) -> bool:
        return True if keyword in self.body else False
