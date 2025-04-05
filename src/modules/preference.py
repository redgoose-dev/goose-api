import os, json

class Preference:

    def __init__(self):
        with open(f'{os.getenv('PATH_ROOT')}data/preference.json', 'r', encoding='utf-8') as file:
            data = json.load(file)
        self.data = data

    def get(self, key: str) -> str|int|bool|None:
        if not key: return None
        if key in self.data: return self.data[key]
        return None
