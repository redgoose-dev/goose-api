import re, sqlite3
from typing import Dict, List, Any
from src.libs.string import color_text

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

    @staticmethod
    def __where_list_to_str__(where: list = None) -> str:
        if not where: return ''
        _where = ' '.join(where)
        _where = re.sub(r'^\s*(and|or|AND|OR)\s+', '', _where.strip())
        if _where: _where = f' WHERE {_where}'
        return _where

    @staticmethod
    def __get_field__(fields: list) -> str:
        return ','.join(fields) if fields else '*'

    @staticmethod
    def __get_limit__(src: dict = None) -> str:
        if not src: return ''
        _size = src['size'] if isinstance(src.get('size'), int) else 24
        _page = src['page'] if isinstance(src.get('page'), int) else 1
        _limit = _size if _size > 0 else 24
        _offset = (_page - 1) * _limit
        return f'LIMIT {_size} OFFSET {_offset}'

    @staticmethod
    def __get_order__(src: dict = None) -> str:
        if not src: return ''
        _order = src['order'] if isinstance(src.get('order'), str) else 'srl'
        _sort = src['sort'] if isinstance(src.get('sort'), str) else 'desc'
        return f'ORDER BY {_order} {_sort}'

    def connect(self):
        self.conn = sqlite3.connect(self.file_path)

    def disconnect(self):
        if self.conn:
            self.conn.close()
            self.conn = None

    def get_table_name(self, name: str = None) -> str|None:
        return self.tables.get(name, None)

    def get_items(
        self,
        table_name: str = None,
        fields: list = None,
        where: list = None,
        values: dict = {},
        limit: dict = None,
        order: dict = None,
        debug: bool = False,
    ) -> list:
        table_name = self.get_table_name(table_name)
        if not table_name: return []
        # set fields
        fields = self.__get_field__(fields)
        # set row factory
        self.conn.row_factory = sqlite3.Row
        cursor = self.conn.cursor()
        # set query
        _where = self.__where_list_to_str__(where)
        _limit = self.__get_limit__(limit)
        _order = self.__get_order__(order)
        sql = f'SELECT {fields} FROM {table_name} {_where} {_order} {_limit}'
        if debug:
            print(color_text(f'[DB_SQL] {sql}', 'yellow'))
            print(color_text(f'[DB_VALUES] {values}', 'yellow'))
        # execute query
        cursor.execute(sql, values)
        rows = cursor.fetchall()
        return [ dict(row) for row in rows ]

    def get_item(
        self,
        table_name: str = None,
        fields: list = None,
        where: list = None,
    ) -> dict|None:
        table_name = self.get_table_name(table_name)
        if not table_name: return None
        # set fields
        fields = self.__get_field__(fields)
        # set cursor
        self.conn.row_factory = sqlite3.Row
        cursor = self.conn.cursor()
        # set query
        query = f'SELECT {fields} FROM {table_name}'
        if where: query += self.__where_list_to_str__(where)
        # execute query
        cursor.execute(query)
        row = cursor.fetchone()
        return dict(row) if row else None

    def get_count(
        self,
        table_name: str = None,
        where: list = None,
    ) -> int:
        table_name = self.get_table_name(table_name)
        if not table_name: return 0
        # set cursor
        cursor = self.conn.cursor()
        # set query
        query = f'SELECT COUNT(*) FROM {table_name}'
        if where: query += self.__where_list_to_str__(where)
        # execute query
        cursor.execute(query)
        return cursor.fetchone()[0]

    def add_item(
        self,
        table_name: str = None,
        placeholders: List[Dict[str, Any]] = None,
        values: Dict[str, str] = None,
    ) -> int|None:
        table_name = self.get_table_name(table_name)
        if not table_name or not placeholders or not values: return None
        # set cursor
        cursor = self.conn.cursor()
        # set query
        columns = ', '.join([item['key'] for item in placeholders])
        placeholders = ', '.join([item['value'] for item in placeholders])
        query = f'INSERT INTO {table_name} ({columns}) VALUES ({placeholders})'
        # execute query
        cursor.execute(query, values)
        self.conn.commit()
        # get last id
        cursor.execute('SELECT last_insert_rowid()')
        # return
        return cursor.fetchone()[0]

    def edit_item(
        self,
        table_name: str = None,
        placeholders: list = None,
        values: dict = None,
        where: list = None,
    ):
        table_name = self.get_table_name(table_name)
        if not table_name or not placeholders or not values: return None
        # set cursor
        self.conn.row_factory = sqlite3.Row
        cursor = self.conn.cursor()
        # set query
        placeholders = ', '.join(placeholders) if placeholders else ''
        query = f'UPDATE {table_name} SET {placeholders}'
        if where: query += self.__where_list_to_str__(where)
        # execute query
        cursor.execute(query, values)
        self.conn.commit()

    def delete_item(
        self,
        table_name: str = None,
        where: list = None,
    ):
        table_name = self.get_table_name(table_name)
        if not table_name: return None
        # set cursor
        self.conn.row_factory = sqlite3.Row
        cursor = self.conn.cursor()
        # set query
        query = f'DELETE FROM {table_name}'
        if where: query += self.__where_list_to_str__(where)
        # execute query
        cursor.execute(query)
        self.conn.commit()

    def get_last_id(self) -> int:
        cursor = self.conn.cursor()
        cursor.execute('SELECT last_insert_rowid()')
        last_id = cursor.fetchone()[0]
        return last_id

