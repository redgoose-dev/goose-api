from src.libs.db import DB, Table
from src.libs.object import json_parse
from ..category import __libs__ as category_libs
from ..article import __libs__ as article_libs

def get_item(_db: DB, srl: int, fields: list = []) -> dict|None:
    if not srl: return None
    data = _db.get_item(
        table_name=Table.NEST.value,
        fields=fields,
        where=[ f'srl = {srl}' ],
    )
    if 'json' in data:
        data['json'] = json_parse(data.get('json', ''))
    return data or None

def get_count(_db: DB, where: list = []) -> int:
    count = _db.get_count(
        table_name=Table.NEST.value,
        where=where,
    )
    return count or 0

# PUBLIC MODULES

def delete(db: DB, srl: int):
    # category data
    category_libs.delete(db, category_libs.Module.NEST, srl)
    # article data
    articles = db.get_items(
        table_name=Table.ARTICLE.value,
        fields=[ 'srl' ],
        where=[ f'nest_srl = {srl}' ],
    )
    for article in articles:
        article_libs.delete(db, article.get('srl'))
    # nest data
    db.delete_item(
        table_name=Table.NEST.value,
        where=[ f'srl = {srl}' ],
    )
