import os, json
from src import libs

class Preference:

    path = f'{libs.data_path}/preference.json'

    def __init__(self):
        with open(self.path, 'r') as file:
            data = json.load(file)
        self.data = data

    def get(self, key: str) -> str|int|bool|None:
        if not key: return None
        if key in self.data: return self.data[key]
        return None

    def get_all(self):
        return self.data

    def update(self, new_data: dict = None, change: bool = False):
        if not new_data or not isinstance(new_data, dict): return
        self.data = new_data if change else { **self.data, **new_data }
        with open(self.path, 'w') as f:
            json.dump(obj=self.data, fp=f, ensure_ascii=False, indent=2)
