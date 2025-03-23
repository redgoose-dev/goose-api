import re, os, sqlite3
from enum import Enum
from typing import Dict, List, Any
from src.libs.string import color_text

class Table(Enum):
    APP = 'goose_app'
    ARTICLE = 'goose_article'
    CATEGORY = 'goose_category'
    CHECKLIST = 'goose_checklist'
    COMMENT = 'goose_comment'
    FILE = 'goose_file'
    JSON = 'goose_json'
    NEST = 'goose_nest'
    PROVIDER = 'goose_provider'
    TOKEN = 'goose_token'

class DB:

    def __init__(self, db_path: str = 'data/db.sqlite'):
        self.file_path = db_path
        self.conn = None
        self.debug = os.getenv('DEBUG') == 'True'

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

    @staticmethod
    def __check_table_name__(name: str = ''):
        if not name or name not in Table._value2member_map_:
            raise Exception('Not found table name.')

    @staticmethod
    def __optimize_query__(query: str) -> str:
        return re.sub(r'\s{2,}', ' ', query).strip()

    def connect(self) -> 'DB':
        self.conn = sqlite3.connect(self.file_path)
        self.conn.row_factory = sqlite3.Row
        if self.debug:
            print(color_text(f'[DB_CONNECT]', 'magenta'))
        return self

    def disconnect(self):
        if self.conn:
            self.conn.close()
            self.conn = None
            if self.debug:
                print(color_text(f'[DB_DISCONNECT]', 'magenta'))

    def get_items(
        self,
        table_name: str = None,
        fields: list = None,
        where: list = None,
        limit: dict = None,
        order: dict = None,
        unlimited: bool = False,
        values: dict = {},
    ) -> list:
        # check table name
        self.__check_table_name__(table_name)
        # set fields
        fields = self.__get_field__(fields)
        # set query
        _where = self.__where_list_to_str__(where) if where else ''
        _limit = self.__get_limit__(limit) if not unlimited else ''
        _order = self.__get_order__(order)
        query = f'SELECT {fields} FROM {table_name} {_where} {_order} {_limit}'
        query = self.__optimize_query__(query)
        if self.debug:
            print_debug('get_items', {
                'DB_SQL': query,
                'DB_VALUES': values,
            })
        # execute query
        cursor = self.conn.cursor()
        cursor.execute(query, values)
        rows = cursor.fetchall()
        return [ dict(row) for row in rows ]

    def get_item(
        self,
        table_name: str = None,
        fields: list = None,
        where: list = None,
        values: dict = {},
    ) -> dict|None:
        # check table name
        self.__check_table_name__(table_name)
        # set fields
        fields = self.__get_field__(fields)
        # set query
        _where = self.__where_list_to_str__(where) if where else ''
        query = f'SELECT {fields} FROM {table_name} {_where}'
        query = self.__optimize_query__(query)
        # print debug
        if self.debug:
            print_debug('get_item', {
                'DB_SQL': query,
                'DB_VALUES': values,
            })
        # execute query
        cursor = self.conn.cursor()
        cursor.execute(query, values)
        row = cursor.fetchone()
        return dict(row) if row else None

    def get_count(
        self,
        table_name: str = None,
        where: list = None,
        values: dict = {},
    ) -> int:
        # check table name
        self.__check_table_name__(table_name)
        # set query
        _where = self.__where_list_to_str__(where) if where else ''
        query = f'SELECT COUNT(*) FROM {table_name} {_where}'
        query = self.__optimize_query__(query)
        # print debug
        if self.debug:
            print_debug('get_count', {
                'DB_SQL': query,
                'DB_VALUES': values,
            })
        # execute query
        cursor = self.conn.cursor()
        cursor.execute(query, values)
        return cursor.fetchone()[0]

    def add_item(
        self,
        table_name: str = None,
        placeholders: List[Dict[str, Any]] = None,
        values: dict = {},
    ) -> int|None:
        # check table name
        self.__check_table_name__(table_name)
        if not placeholders or not values: return None
        # set query
        columns = ', '.join([item['key'] for item in placeholders])
        placeholders = ', '.join([item['value'] for item in placeholders])
        query = f'INSERT INTO {table_name} ({columns}) VALUES ({placeholders})'
        query = self.__optimize_query__(query)
        # print debug
        if self.debug:
            print_debug('add_item', {
                'DB_SQL': query,
                'DB_VALUES': values,
            })
        # execute query
        cursor = self.conn.cursor()
        cursor.execute(query, values)
        self.conn.commit()
        # get last id
        cursor.execute('SELECT last_insert_rowid()')
        # return
        return cursor.fetchone()[0]

    def update_item(
        self,
        table_name: str = None,
        placeholders: list = None,
        where: list = None,
        values: dict = {},
    ):
        # check table name
        self.__check_table_name__(table_name)
        if not placeholders or not values: return None
        # set query
        placeholders = ', '.join(placeholders) if placeholders else ''
        _where = self.__where_list_to_str__(where) if where else ''
        query = f'UPDATE {table_name} SET {placeholders} {_where}'
        query = self.__optimize_query__(query)
        # print debug
        if self.debug:
            print_debug('update_item', {
                'DB_SQL': query,
                'DB_VALUES': values,
            })
        # execute query
        cursor = self.conn.cursor()
        cursor.execute(query, values)
        self.conn.commit()

    def delete_item(
        self,
        table_name: str = None,
        where: list = None,
        values: dict = {},
    ):
        # check table name
        self.__check_table_name__(table_name)
        # set query
        _where = self.__where_list_to_str__(where) if where else ''
        query = f'DELETE FROM {table_name} {_where}'
        query = self.__optimize_query__(query)
        # print debug
        if self.debug:
            print_debug('delete_item', {
                'DB_SQL': query,
                'DB_VALUES': values,
            })
        # execute query
        cursor = self.conn.cursor()
        cursor.execute(query, values)
        self.conn.commit()

    def get_last_id(self) -> int:
        cursor = self.conn.cursor()
        cursor.execute('SELECT last_insert_rowid()')
        last_id = cursor.fetchone()[0]
        return last_id

    def get_max_number(
        self,
        table_name: str,
        field_name: str,
        where: list,
        values: dict = {},
    ) -> int:
        # check table name
        self.__check_table_name__(table_name)
        # set query
        _where = self.__where_list_to_str__(where) if where else ''
        query = f'SELECT MAX({field_name}) as number FROM {table_name} {_where}'
        query = self.__optimize_query__(query)
        # print debug
        if self.debug:
            print_debug('get_max_number', {
                'DB_SQL': query,
                'DB_WHERE': where,
            })
        # execute query
        cursor = self.conn.cursor()
        result = cursor.execute(query, values).fetchone()
        return result['number'] if result['number'] is not None else 0

    def query(self, query: str = '', values: dict = {}) -> dict:
        # set cursor
        cursor = self.conn.cursor()
        # print debug
        if self.debug:
            print_debug('run_query', {
                'DB_SQL': query,
                'DB_VALUES': values,
            })
        # execute query
        cursor.execute(query, values)
        self.conn.commit()
        return cursor

def print_debug(method: str, data: dict):
    print(color_text(f'[DB_METHOD] {method}:', 'magenta'))
    for key, value in data.items():
        if value: print(color_text(f'  [{key}] {value}', 'magenta'))
