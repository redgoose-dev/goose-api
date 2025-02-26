import sqlite3

class DB:

    # table names
    tables = {
        'app': 'goose_app',
        'article': 'goose_article',
        'category': 'goose_category',
        'checklist': 'goose_checklist',
        'comment': 'goose_comment',
        'file': 'goose_file',
        'json': 'goose_json',
        'nest': 'goose_nest',
        'provider': 'goose_provider',
        'token': 'goose_token',
    }

    def __init__(self, db_path: str = 'data/db.sqlite'):
        self.file_path = db_path
        self.conn = None

    def connect(self):
        self.conn = sqlite3.connect(self.file_path)

    def disconnect(self):
        if self.conn:
            self.conn.close()
            self.conn = None

    def get_table_name(self, name: str = None) -> str|None:
        return self.tables.get(name, None)

    def get_items(self, table_name: str = None, where: str = None) -> list:
        table_name = self.get_table_name(table_name)
        if not table_name: return []
        # set row factory
        self.conn.row_factory = sqlite3.Row
        cursor = self.conn.cursor()
        # set query
        query = f'SELECT * FROM {table_name}'
        if where: query += f' WHERE {where}'
        # execute query
        cursor.execute(query)
        rows = cursor.fetchall()
        return [ dict(row) for row in rows ]

    def get_item(self, table_name: str = None, where: str = None) -> dict|None:
        table_name = self.get_table_name(table_name)
        if not table_name: return None
        # set cursor
        self.conn.row_factory = sqlite3.Row
        cursor = self.conn.cursor()
        # set query
        query = f'SELECT * FROM {table_name}'
        if where: query += f' WHERE {where}'
        # execute query
        cursor.execute(query)
        row = cursor.fetchone()
        return dict(row) if row else None

    def get_count(self, table_name: str = None, where: str = None) -> int:
        table_name = self.get_table_name(table_name)
        if not table_name: return 0
        return 0

    def add_item(self, table_name: str = None, data: list = []) -> int|None:
        table_name = self.get_table_name(table_name)
        if not table_name or not data: return None
        # set cursor
        cursor = self.conn.cursor()
        # set query
        columns = ', '.join([item['key'] for item in data])
        placeholders = ', '.join([item.get('key_name', '?') if 'value' in item else item['key_name'] for item in data])
        values = tuple(item['value'] for item in data if 'value' in item)
        query = f'INSERT INTO {table_name} ({columns}) VALUES ({placeholders})'
        # execute query
        cursor.execute(query, values)
        self.conn.commit()
        # get last id
        cursor.execute('SELECT last_insert_rowid()')
        # return
        return cursor.fetchone()[0]

    def edit_item(self):
        pass

    def remove_item(self):
        pass

    # def get_last_id(self) -> int:
    #     cursor = self.conn.cursor()
    #     cursor.execute('SELECT last_insert_rowid()')
    #     last_id = cursor.fetchone()[0]
    #     return last_id



