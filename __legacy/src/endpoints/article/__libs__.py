from src.libs.db import DB, Table
from ..file import __libs__ as file_libs
from ..comment import __libs__ as comment_libs
from ..tag import __libs__ as tag_libs

class Status:
    READY = 'ready'
    PUBLIC = 'public'
    PRIVATE = 'private'
    @staticmethod
    def check(value):
        return value in [ Status.PUBLIC, Status.PRIVATE ]

def get_count(_db: DB, where: list = []) -> int:
    count = _db.get_count(
        table_name = Table.ARTICLE.value,
        where = where,
    )
    return count or 0

# PUBLIC MODULES

def delete(db: DB, srl: int):
    # file data
    file_libs.delete(db, file_libs.Module.ARTICLE, srl)
    # comment data
    comment_libs.delete_with_module(db, comment_libs.Module.ARTICLE, srl)
    # tag data
    tag_libs.delete(db, tag_libs.Module.ARTICLE, srl)
    # article data
    db.delete_item(
        table_name=Table.ARTICLE.value,
        where=[ f'srl = {srl}' ],
    )
